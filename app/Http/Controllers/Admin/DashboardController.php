<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerPaid;
use App\Models\Product;
use App\Models\Staff;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $data['totalRevenueUsd'] = 23;
        $data['totalRevenueKhr'] =  23;
        $data['totalRevenueThb'] = 23;

        $data['totalExpenseUsd'] =  23;
        $data['totalExpenseKhr'] =  23;
        $data['totalExpenseThb'] =  23;

        $data['totalCustomerPaidUsd'] = CustomerPaid::sum('amount_usd');
        $data['totalCustomerPaidKhr'] = CustomerPaid::sum('amount_kh');
        $data['totalOrderRemainingAmount'] = (float) Order::whereIn('payment_status', ['Pending', 'Partial'])
            ->sum(DB::raw('GREATEST(0, total_price - COALESCE(paid_amount, 0))'));
        $data['remainingOrderCount'] = (int) Order::whereIn('payment_status', ['Pending', 'Partial'])
            ->whereRaw('(total_price - COALESCE(paid_amount, 0)) > 0')
            ->count();
        
        $data['totalOrderPrice'] = (float) Order::sum('total_price');
        $data['pendingOrderCount'] = (int) Order::where('payment_status', 'Pending')->count();

        // Products Count Dynamic Data
        $data['totalProductsCount'] = (int) Product::count();
        $data['totalProductCount'] = $data['totalProductsCount'];
        $data['activeProductsCount'] = (int) Product::where('status', 1)->count();
        $data['activeProductCount'] = $data['activeProductsCount'];

        // Customer Count Dynamic Data
        $data['totalCustomerCount'] = (int) Customer::count();
        $data['totalCustomersCount'] = $data['totalCustomerCount'];
        $data['activeCustomerCount'] = (int) Customer::where('status', 1)->count();
        $data['activeCustomersCount'] = $data['activeCustomerCount'];

        // Staff Count Dynamic Data
        $data['totalStaffCount'] = (int) Staff::count();
        $data['activeStaffCount'] = (int) Staff::where('status', 1)->count();

        // User Count Dynamic Data
        $data['totalUserCount'] = (int) User::count();
        $data['totalUsersCount'] = $data['totalUserCount'];
        $data['activeUserCount'] = (int) User::where('status', 1)->count();
        $data['activeUsersCount'] = $data['activeUserCount'];

        $data['recentTransactions'] = Order::with(['customer', 'shop'])
            ->latest('id')
            ->take(6)
            ->get();

        // Weekly Stats Dynamic Data
        $data['weeklyStats'] = $this->getWeeklyStatsData();

        if ($request->get('ajax') === 'weekly_stats') {
            return response()->json($data['weeklyStats']);
        }

        // Product Performance Dynamic Data
        $selectedMonth = $request->get('product_month', $request->get('month', 'all'));
        $data['productPerformance'] = $this->getProductPerformanceData($selectedMonth);
        $data['productPerformanceMonths'] = $this->getProductPerformanceMonths();
        $data['selectedProductMonth'] = $selectedMonth;
        $data['selectedPerformanceMonth'] = $selectedMonth;

        // If AJAX request specifically for product performance filter
        if ($request->ajax() || $request->get('ajax') === 'product_performance') {
            return response()->json([
                'html' => view('admin::pages.dashboard_product_performance_rows', [
                    'productPerformance' => $data['productPerformance'],
                ])->render(),
            ]);
        }

        // Yearly Sales Dynamic Data
        $availableYears = $this->getYearlySalesAvailableYears();
        $selectedYear = (int) ($request->get('year') ?: ($availableYears[0] ?? (int) Carbon::now()->year));
        $data['yearlySalesAvailableYears'] = $availableYears;
        $data['selectedYearlySalesYear'] = $selectedYear;
        $data['yearlySales'] = $this->getYearlySalesData($selectedYear);

        // If AJAX request specifically for yearly sales filter
        if ($request->get('ajax') === 'yearly_sales') {
            return response()->json($data['yearlySales']);
        }

        return view('admin::pages.dashboard', $data);
    }

    private function getProductPerformanceMonths()
    {
        $months = [];
        $now = Carbon::now();

        for ($i = 0; $i < 6; $i++) {
            $date = (clone $now)->subMonths($i);
            $monthKey = $date->format('Y-m');
            $monthFull = strtolower($date->format('F'));
            $year = $date->format('Y');

            $localizedMonth = __("dashboard.months.{$monthFull}");
            if ($localizedMonth === "dashboard.months.{$monthFull}") {
                $localizedMonth = $date->format('F');
            }

            $monthLabel = $localizedMonth . ' ' . $year;

            $months[] = [
                'value' => $monthKey,
                'label' => $monthLabel,
            ];
        }

        return $months;
    }

    private function getProductPerformanceData($selectedMonth = 'all')
    {
        $query = DB::table('order_details')
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->join('products', 'products.id', '=', 'order_details.product_id')
            ->leftJoin('categories', function ($join) {
                $join->on('categories.id', '=', 'products.category_id')
                    ->whereNull('categories.deleted_at');
            })
            ->whereNull('order_details.deleted_at')
            ->whereNull('orders.deleted_at')
            ->whereNull('products.deleted_at')
            ->where('orders.payment_status', '!=', 'Cancel');

        if ($selectedMonth && $selectedMonth !== 'all') {
            try {
                $startDate = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
                $endDate = (clone $startDate)->endOfMonth();
                $query->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween(DB::raw('DATE(orders.order_date)'), [$startDate->toDateString(), $endDate->toDateString()])
                      ->orWhere(function ($sub) use ($startDate, $endDate) {
                          $sub->whereNull('orders.order_date')
                              ->whereBetween(DB::raw('DATE(orders.created_at)'), [$startDate->toDateString(), $endDate->toDateString()]);
                      });
                });
            } catch (\Exception $e) {
                // Ignore invalid month format
            }
        }

        $topProducts = $query->select(
                'products.id as product_id',
                'products.name as product_name',
                'products.image as product_image',
                'products.price as product_price',
                'products.cost as product_cost',
                'categories.name as category_name',
                DB::raw('SUM(order_details.qty) as total_qty'),
                DB::raw('SUM((order_details.price * order_details.qty) - COALESCE(order_details.product_discount, 0)) as total_revenue')
            )
            ->groupBy(
                'products.id',
                'products.name',
                'products.image',
                'products.price',
                'products.cost',
                'categories.name'
            )
            ->orderByDesc('total_revenue')
            ->take(5)
            ->get();

        // If 'all' is selected and we have fewer than 4 products from sales, supplement with catalog products
        if ($selectedMonth === 'all' && $topProducts->count() < 4) {
            $existingIds = $topProducts->pluck('product_id')->toArray();
            $needed = 4 - $topProducts->count();

            $fillProducts = DB::table('products')
                ->leftJoin('categories', function ($join) {
                    $join->on('categories.id', '=', 'products.category_id')
                        ->whereNull('categories.deleted_at');
                })
                ->whereNull('products.deleted_at')
                ->where('products.status', 1)
                ->whereNotIn('products.id', $existingIds)
                ->select(
                    'products.id as product_id',
                    'products.name as product_name',
                    'products.image as product_image',
                    'products.price as product_price',
                    'products.cost as product_cost',
                    'categories.name as category_name',
                    DB::raw('0 as total_qty'),
                    DB::raw('0 as total_revenue')
                )
                ->take($needed)
                ->get();

            $topProducts = $topProducts->concat($fillProducts);
        }

        if ($topProducts->isEmpty()) {
            return collect();
        }

        $maxRevenue = (float) $topProducts->max('total_revenue');
        $colors = ['yellow', 'mint', 'gray', 'pink', 'blue', 'cyan'];

        $icons = [
            '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#2A3547" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
            '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#2A3547" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>',
            '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#2A3547" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>',
            '<svg width="22" height="22" viewBox="0 0 24 24" fill="#2A3547"><path d="M21 6H3c-1.1 0-2 .9-2 2v8c0 1.1.9 2 2 2h18c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-10 7H8v3H6v-3H3v-2h3V8h2v3h3v2zm4.5 2c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm3-3c-.83 0-1.5-.67-1.5-1.5S17.67 9 18.5 9s1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/></svg>',
            '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#2A3547" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
        ];

        $sparklines = [
            'very_high' => [
                'path' => 'M2 22 Q 22 4, 45 16 T 88 6',
                'color' => '#5D87FF',
            ],
            'high' => [
                'path' => 'M2 18 Q 20 2, 45 18 T 88 10',
                'color' => '#13DEB9',
            ],
            'medium' => [
                'path' => 'M2 15 Q 25 6, 50 22 T 88 14',
                'color' => '#FFAE1F',
            ],
            'low' => [
                'path' => 'M2 15 Q 22 24, 45 12 T 88 16',
                'color' => '#CBD5E1',
            ],
        ];

        return $topProducts->values()->map(function ($item, $index) use ($maxRevenue, $colors, $icons, $sparklines) {
            $revenue = (float) $item->total_revenue;
            $price = (float) ($item->product_price ?? 0);
            $cost = (float) ($item->product_cost ?? 0);
            if ($cost <= 0) {
                $cost = $price * 0.65;
            }
            $qty = (int) ($item->total_qty ?? 0);

            // Progress Calculation
            if ($maxRevenue > 0) {
                $progress = round(($revenue / $maxRevenue) * 100, 1);
                $progress = max(15.0, min(98.5, $progress));
            } elseif ($price > 0 && $cost > 0) {
                $margin = (($price - $cost) / $price) * 100;
                $progress = round(max(20.0, min(95.0, $margin)), 1);
            } else {
                $progress = [78.5, 58.6, 45.0, 32.0][$index % 4];
            }

            // Priority determination
            if ($progress >= 75) {
                $priority = 'very_high';
                $badgeClass = 'badge-blue';
            } elseif ($progress >= 50) {
                $priority = 'high';
                $badgeClass = 'badge-coral';
            } elseif ($progress >= 30) {
                $priority = 'medium';
                $badgeClass = 'badge-orange';
            } else {
                $priority = 'low';
                $badgeClass = 'badge-teal';
            }

            // Budget / sales display
            $displayAmount = $revenue > 0 ? $revenue : $price;
            if ($displayAmount >= 1000) {
                $budgetFormatted = '$' . number_format($displayAmount / 1000, 1) . 'k';
            } else {
                $budgetFormatted = '$' . number_format($displayAmount, 2);
            }

            $imageUrl = null;
            if (!empty($item->product_image)) {
                $imageUrl = str_starts_with($item->product_image, 'http')
                    ? $item->product_image
                    : url('file_manager' . $item->product_image);
            }

            $profit = max(0, $revenue - ($cost * $qty));

            return [
                'product_id' => $item->product_id,
                'name' => $item->product_name ?? ('Product ' . ($index + 1)),
                'category' => $item->category_name ?? __('dashboard.uncategorized'),
                'image_url' => $imageUrl,
                'thumb_color' => $colors[$index % count($colors)],
                'vector_icon' => $icons[$index % count($icons)],
                'progress' => $progress,
                'priority' => $priority,
                'badge_class' => $badgeClass,
                'budget' => $budgetFormatted,
                'raw_revenue' => $displayAmount,
                'sparkline_path' => $sparklines[$priority]['path'],
                'sparkline_color' => $sparklines[$priority]['color'],

                // Also support legacy/alternative keys for resilience
                'rank' => $index + 1,
                'badgeClass' => $badgeClass,
                'image' => $imageUrl ?: asset('images/logo/default.png'),
                'cost' => $cost,
                'cost_formatted' => '$' . number_format($cost, 2),
                'revenue' => $revenue,
                'revenue_formatted' => '$' . number_format($revenue, 2),
                'profit' => $profit,
                'profit_formatted' => '$' . number_format($profit, 2),
                'margin' => $revenue > 0 ? round(($profit / $revenue) * 100, 1) : 0,
                'units_sold' => $qty,
            ];
        });
    }

    private function getYearlySalesAvailableYears()
    {
        $currentYear = (int) Carbon::now()->year;

        $orderYears = DB::table('orders')
            ->whereNull('deleted_at')
            ->select(DB::raw('YEAR(order_date) as yr'))
            ->whereNotNull('order_date')
            ->groupBy('yr')
            ->pluck('yr')
            ->map(fn($y) => (int) $y)
            ->toArray();

        $expenseYears = [];
        if (Schema::hasTable('staff_expenses')) {
            $expenseYears = DB::table('staff_expenses')
                ->whereNull('deleted_at')
                ->whereNotNull('expense_date')
                ->select(DB::raw('YEAR(expense_date) as yr'))
                ->groupBy('yr')
                ->pluck('yr')
                ->map(fn($y) => (int) $y)
                ->toArray();
        }

        $years = array_unique(array_merge([$currentYear, $currentYear - 1], $orderYears, $expenseYears));
        rsort($years);

        return array_values($years);
    }

    private function getYearlySalesData(int $year)
    {
        $startOfYear = Carbon::createFromDate($year, 1, 1)->startOfYear()->toDateTimeString();
        $endOfYear = Carbon::createFromDate($year, 12, 31)->endOfYear()->toDateTimeString();

        $monthlySalesRaw = DB::table('orders')
            ->whereNull('deleted_at')
            ->where('payment_status', '!=', 'Cancel')
            ->whereBetween(DB::raw('DATE(order_date)'), [$startOfYear, $endOfYear])
            ->select(
                DB::raw('MONTH(order_date) as month_num'),
                DB::raw('SUM(total_price) as total_sales')
            )
            ->groupBy('month_num')
            ->pluck('total_sales', 'month_num')
            ->toArray();

        $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear()->toDateString();
        $endDate = Carbon::createFromDate($year, 12, 31)->endOfYear()->toDateString();

        $salaryTotal = 0.0;
        $otherExpenseTotal = 0.0;
        $totalExpense = 0.0;

        if (Schema::hasTable('staff_expenses')) {
            $monthlySalaryRaw = DB::table('staff_expenses')
                ->whereNull('deleted_at')
                ->where('type', 'Salary')
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->select(
                    DB::raw('MONTH(expense_date) as month_num'),
                    DB::raw('SUM(amount) as total_amount')
                )
                ->groupBy('month_num')
                ->pluck('total_amount', 'month_num')
                ->toArray();

            $monthlyOtherRaw = DB::table('staff_expenses')
                ->whereNull('deleted_at')
                ->where('type', '!=', 'Salary')
                ->whereBetween('expense_date', [$startDate, $endDate])
                ->select(
                    DB::raw('MONTH(expense_date) as month_num'),
                    DB::raw('SUM(amount) as total_amount')
                )
                ->groupBy('month_num')
                ->pluck('total_amount', 'month_num')
                ->toArray();

            $salaryTotal = (float) array_sum($monthlySalaryRaw);
            $otherExpenseTotal = (float) array_sum($monthlyOtherRaw);
            $totalExpense = $salaryTotal + $otherExpenseTotal;
        }

        $monthKeys = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
        $salesSeries = [];
        $salarySeries = [];
        $otherSeries = [];
        $categories = [];
        $totalSales = 0.0;

        for ($m = 1; $m <= 12; $m++) {
            $salesVal = (float) ($monthlySalesRaw[$m] ?? 0);
            $salaryVal = (float) ($monthlySalaryRaw[$m] ?? 0);
            $otherVal = (float) ($monthlyOtherRaw[$m] ?? 0);

            $totalSales += $salesVal;

            $salesSeries[] = round($salesVal, 2);
            $salarySeries[] = round($salaryVal, 2);
            $otherSeries[] = round($otherVal, 2);

            $key = $monthKeys[$m - 1];
            $monthLabel = __("dashboard.months.{$key}");
            if ($monthLabel === "dashboard.months.{$key}") {
                $monthLabel = ucfirst($key);
            }
            $categories[] = $monthLabel;
        }

        $now = Carbon::now();
        $isCurrentYear = ($year === (int) $now->year);
        $elapsedMonths = $isCurrentYear ? (int) $now->month : 12;
        $elapsedMonths = max(1, $elapsedMonths);

        $avgSales = $totalSales / $elapsedMonths;
        $netProfit = $totalSales - $totalExpense;

        return [
            'year' => $year,
            'total_sales' => $totalSales,
            'total_sales_formatted' => '$' . number_format($totalSales, 2),
            'avg_sales' => round($avgSales, 2),
            'avg_sales_formatted' => '$' . number_format($avgSales, 2),
            'total_expense' => $totalExpense,
            'total_expense_formatted' => '$' . number_format($totalExpense, 2),
            'salary_expense' => $salaryTotal,
            'salary_expense_formatted' => '$' . number_format($salaryTotal, 2),
            'other_expense' => $otherExpenseTotal,
            'other_expense_formatted' => '$' . number_format($otherExpenseTotal, 2),
            'net_profit' => $netProfit,
            'net_profit_formatted' => '$' . number_format($netProfit, 2),
            'categories' => $categories,
            'series' => [
                [
                    'name' => __('dashboard.yearly_sales_legend_sales'),
                    'data' => $salesSeries,
                ],
                [
                    'name' => __('dashboard.yearly_sales_legend_salary'),
                    'data' => $salarySeries,
                ],
                [
                    'name' => __('dashboard.yearly_sales_legend_other'),
                    'data' => $otherSeries,
                ],
            ],
        ];
    }

    private function getWeeklyStatsData(?Carbon $date = null)
    {
        $now = $date ? (clone $date) : Carbon::now();
        $startOfWeek = (clone $now)->startOfWeek(); // Monday 00:00:00
        $endOfWeek = (clone $now)->endOfWeek();     // Sunday 23:59:59

        $startDate = $startOfWeek->toDateTimeString();
        $endDate = $endOfWeek->toDateTimeString();

        // 1. Daily Sales for current week (Mon -> Sun)
        $dailySalesRaw = DB::table('orders')
            ->whereNull('deleted_at')
            ->where('payment_status', '!=', 'Cancel')
            ->whereBetween(DB::raw('DATE(order_date)'), [$startDate, $endDate])
            ->select(
                DB::raw('DATE(order_date) as sale_date'),
                DB::raw('SUM(total_price) as total_sales')
            )
            ->groupBy('sale_date')
            ->pluck('total_sales', 'sale_date')
            ->toArray();

        $dayKeys = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
        $chartData = [];
        $chartCategories = [];
        $totalWeeklySales = 0.0;

        for ($i = 0; $i < 7; $i++) {
            $dayDate = (clone $startOfWeek)->addDays($i)->toDateString();
            $val = (float) ($dailySalesRaw[$dayDate] ?? 0);
            $totalWeeklySales += $val;

            $key = $dayKeys[$i];
            $dayLabel = __("dashboard.days_short.{$key}");
            if ($dayLabel === "dashboard.days_short.{$key}") {
                $dayLabel = ucfirst($key);
            }

            $chartCategories[] = $dayLabel;
            $chartData[] = round($val, 2);
        }

        $averageSales = round($totalWeeklySales / 7, 2);

        // 2. Top Sales (Barber / Staff)
        $topStaff = null;
        if (Schema::hasTable('barbers') && Schema::hasColumn('orders', 'barber_id')) {
            // Attempt 1: Current week top barber
            $topStaff = DB::table('orders')
                ->join('barbers', 'barbers.id', '=', 'orders.barber_id')
                ->whereNull('orders.deleted_at')
                ->whereNull('barbers.deleted_at')
                ->where('orders.payment_status', '!=', 'Cancel')
                ->whereBetween(DB::raw('DATE(orders.order_date)'), [$startDate, $endDate])
                ->select(
                    'barbers.name as name',
                    DB::raw('COUNT(orders.id) as total_count'),
                    DB::raw('SUM(orders.total_price) as total_sales')
                )
                ->groupBy('barbers.id', 'barbers.name')
                ->orderByDesc('total_sales')
                ->first();

            // Fallback 1: All-time top barber from orders if none in current week
            if (!$topStaff) {
                $topStaff = DB::table('orders')
                    ->join('barbers', 'barbers.id', '=', 'orders.barber_id')
                    ->whereNull('orders.deleted_at')
                    ->whereNull('barbers.deleted_at')
                    ->where('orders.payment_status', '!=', 'Cancel')
                    ->select(
                        'barbers.name as name',
                        DB::raw('COUNT(orders.id) as total_count'),
                        DB::raw('SUM(orders.total_price) as total_sales')
                    )
                    ->groupBy('barbers.id', 'barbers.name')
                    ->orderByDesc('total_sales')
                    ->first();
            }
        }

        // Fallback 2: First active staff or barber if no orders associated
        if (!$topStaff) {
            if (Schema::hasTable('barbers')) {
                $firstBarber = DB::table('barbers')->whereNull('deleted_at')->where('status', 1)->first();
                if ($firstBarber) {
                    $topStaff = (object) [
                        'name' => $firstBarber->name,
                        'total_count' => 0,
                        'total_sales' => 0,
                    ];
                }
            }
            if (!$topStaff && Schema::hasTable('staffs')) {
                $firstStaff = DB::table('staffs')->whereNull('deleted_at')->where('status', 1)->first();
                if ($firstStaff) {
                    $topStaff = (object) [
                        'name' => $firstStaff->name,
                        'total_count' => 0,
                        'total_sales' => 0,
                    ];
                }
            }
        }

        $topSalesName = $topStaff?->name ?: 'Johnathan Doe';
        $topSalesCount = $topStaff ? (int) $topStaff->total_count : 0;
        $topSalesBadge = '+' . $topSalesCount;

        // 3. Best Seller (Product / Service)
        $bestProduct = null;
        if (Schema::hasTable('products')) {
            // Attempt 1: Current week best-selling product
            $bestProduct = DB::table('order_details')
                ->join('orders', 'orders.id', '=', 'order_details.order_id')
                ->join('products', 'products.id', '=', 'order_details.product_id')
                ->whereNull('order_details.deleted_at')
                ->whereNull('orders.deleted_at')
                ->whereNull('products.deleted_at')
                ->where('orders.payment_status', '!=', 'Cancel')
                ->whereBetween(DB::raw('DATE(orders.order_date)'), [$startDate, $endDate])
                ->select(
                    'products.name as name',
                    DB::raw('SUM(order_details.qty) as total_qty'),
                    DB::raw('SUM((order_details.price * order_details.qty) - COALESCE(order_details.product_discount, 0)) as total_sales')
                )
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('total_qty')
                ->first();

            // Fallback 1: All-time best-selling product
            if (!$bestProduct) {
                $bestProduct = DB::table('order_details')
                    ->join('orders', 'orders.id', '=', 'order_details.order_id')
                    ->join('products', 'products.id', '=', 'order_details.product_id')
                    ->whereNull('order_details.deleted_at')
                    ->whereNull('orders.deleted_at')
                    ->whereNull('products.deleted_at')
                    ->where('orders.payment_status', '!=', 'Cancel')
                    ->select(
                        'products.name as name',
                        DB::raw('SUM(order_details.qty) as total_qty'),
                        DB::raw('SUM((order_details.price * order_details.qty) - COALESCE(order_details.product_discount, 0)) as total_sales')
                    )
                    ->groupBy('products.id', 'products.name')
                    ->orderByDesc('total_qty')
                    ->first();
            }
        }

        // Fallback 2: Check services if no products were sold
        if (!$bestProduct && Schema::hasTable('services')) {
            $bestService = DB::table('order_details')
                ->join('orders', 'orders.id', '=', 'order_details.order_id')
                ->join('services', 'services.id', '=', 'order_details.service_id')
                ->whereNull('order_details.deleted_at')
                ->whereNull('orders.deleted_at')
                ->whereNull('services.deleted_at')
                ->where('orders.payment_status', '!=', 'Cancel')
                ->select(
                    'services.name as name',
                    DB::raw('SUM(order_details.qty) as total_qty')
                )
                ->groupBy('services.id', 'services.name')
                ->orderByDesc('total_qty')
                ->first();
            if ($bestService) {
                $bestProduct = $bestService;
            }
        }

        // Fallback 3: First active product in catalog
        if (!$bestProduct && Schema::hasTable('products')) {
            $firstProduct = DB::table('products')->whereNull('deleted_at')->where('status', 1)->first();
            if ($firstProduct) {
                $bestProduct = (object) [
                    'name' => $firstProduct->name,
                    'total_qty' => 0,
                ];
            }
        }

        $bestSellerName = $bestProduct?->name ?: __('dashboard.footware');
        $bestSellerCount = $bestProduct ? (int) $bestProduct->total_qty : 0;
        $bestSellerBadge = '+' . $bestSellerCount;

        // 4. Most Commented / Most Popular Category
        $topCategory = null;
        if (Schema::hasTable('products') && Schema::hasTable('categories')) {
            // Attempt 1: Current week top category
            $topCategory = DB::table('order_details')
                ->join('orders', 'orders.id', '=', 'order_details.order_id')
                ->join('products', 'products.id', '=', 'order_details.product_id')
                ->join('categories', 'categories.id', '=', 'products.category_id')
                ->whereNull('order_details.deleted_at')
                ->whereNull('orders.deleted_at')
                ->whereNull('products.deleted_at')
                ->whereNull('categories.deleted_at')
                ->where('orders.payment_status', '!=', 'Cancel')
                ->whereBetween(DB::raw('DATE(orders.order_date)'), [$startDate, $endDate])
                ->select(
                    'categories.name as name',
                    DB::raw('SUM(order_details.qty) as total_qty')
                )
                ->groupBy('categories.id', 'categories.name')
                ->orderByDesc('total_qty')
                ->first();

            // Fallback 1: All-time top category
            if (!$topCategory) {
                $topCategory = DB::table('order_details')
                    ->join('orders', 'orders.id', '=', 'order_details.order_id')
                    ->join('products', 'products.id', '=', 'order_details.product_id')
                    ->join('categories', 'categories.id', '=', 'products.category_id')
                    ->whereNull('order_details.deleted_at')
                    ->whereNull('orders.deleted_at')
                    ->whereNull('products.deleted_at')
                    ->whereNull('categories.deleted_at')
                    ->where('orders.payment_status', '!=', 'Cancel')
                    ->select(
                        'categories.name as name',
                        DB::raw('SUM(order_details.qty) as total_qty')
                    )
                    ->groupBy('categories.id', 'categories.name')
                    ->orderByDesc('total_qty')
                    ->first();
            }
        }

        // Fallback 2: First active category in catalog
        if (!$topCategory && Schema::hasTable('categories')) {
            $firstCategory = DB::table('categories')->whereNull('deleted_at')->where('status', 1)->first();
            if ($firstCategory) {
                $topCategory = (object) [
                    'name' => $firstCategory->name,
                    'total_qty' => 0,
                ];
            }
        }

        $mostCommentedName = $topCategory?->name ?: __('dashboard.fashionware');
        $mostCommentedCount = $topCategory ? (int) $topCategory->total_qty : 0;
        $mostCommentedBadge = '+' . $mostCommentedCount;

        return [
            'total_sales' => $totalWeeklySales,
            'total_sales_formatted' => '$' . number_format($totalWeeklySales, 2),
            'average_sales' => $averageSales,
            'average_sales_formatted' => '$' . number_format($averageSales, 2),
            'chart_data' => $chartData,
            'chart_categories' => $chartCategories,
            'top_sales' => [
                'name' => $topSalesName,
                'count' => $topSalesCount,
                'badge' => $topSalesBadge,
            ],
            'best_seller' => [
                'name' => $bestSellerName,
                'count' => $bestSellerCount,
                'badge' => $bestSellerBadge,
            ],
            'most_commented' => [
                'name' => $mostCommentedName,
                'count' => $mostCommentedCount,
                'badge' => $mostCommentedBadge,
            ],
        ];
    }
}
