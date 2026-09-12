<?php

namespace App\Http\Controllers\Admin\Report;

use App\Enums\ExpenseType;
use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\Staff;
use App\Models\StaffExpense;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffExpenseReportController extends Controller
{
    protected $layout = 'admin::pages.report.staffExpense.';
    private $routeName = 'report-staff-expense';

    public function __construct()
    {
        $this->middleware('permission:report-staff-expense-view|staff-expense-view|report-sales-view|booking-view', [
            'only' => ['index', 'daily', 'monthly', 'report', 'details']
        ]);
    }

    /**
     * Entry point: Default to Daily view
     */
    public function index(Request $req)
    {
        return redirect()->route('admin-' . $this->routeName . '-daily', $req->query());
    }

    /**
     * Daily Staff Expense Report View
     */
    public function daily(Request $req)
    {
        $dates = $this->resolveDailyDateRange($req);
        $appliedFilters = $this->extractFilters($req, 'daily');

        $baseQuery = $this->buildFilteredExpenseQuery($req, $dates['from'], $dates['to']);

        // Overall summary statistics for filtered date range
        $summary = $this->calculateSummaryMetrics(clone $baseQuery, $dates['from'], $dates['to']);

        // Daily aggregated breakdown
        $dailyRows = $this->aggregateDailyExpenses(clone $baseQuery);

        $filterOptions = $this->getFilterOptions();

        return view($this->layout . 'index', [
            'viewMode'     => 'daily',
            'routeName'    => $this->routeName,
            'from_date'    => $dates['from'],
            'to_date'      => $dates['to'],
            'filters'      => $appliedFilters,
            'summary'      => $summary,
            'rows'         => $dailyRows,
            'staffList'    => $filterOptions['staffList'],
            'shops'        => $filterOptions['shops'],
            'expenseTypes' => $filterOptions['expenseTypes'],
        ]);
    }

    /**
     * Monthly Staff Expense Report View
     */
    public function monthly(Request $req)
    {
        $monthRange = $this->resolveMonthlyDateRange($req);
        $appliedFilters = $this->extractFilters($req, 'monthly');

        $baseQuery = $this->buildFilteredExpenseQuery($req, $monthRange['from'], $monthRange['to']);

        // Overall summary statistics for filtered monthly range
        $summary = $this->calculateSummaryMetrics(clone $baseQuery, $monthRange['from'], $monthRange['to']);

        // Monthly aggregated breakdown
        $monthlyRows = $this->aggregateMonthlyExpenses(clone $baseQuery);

        $filterOptions = $this->getFilterOptions();

        return view($this->layout . 'index', [
            'viewMode'       => 'monthly',
            'routeName'      => $this->routeName,
            'selectedYear'   => $monthRange['year'],
            'from_month'     => $monthRange['from_month'],
            'to_month'       => $monthRange['to_month'],
            'from_date'      => $monthRange['from'],
            'to_date'        => $monthRange['to'],
            'filters'        => $appliedFilters,
            'summary'        => $summary,
            'rows'           => $monthlyRows,
            'staffList'      => $filterOptions['staffList'],
            'shops'          => $filterOptions['shops'],
            'expenseTypes'   => $filterOptions['expenseTypes'],
            'availableYears' => $this->getAvailableYears(),
        ]);
    }

    /**
     * API / JSON endpoint for export and dynamic charts
     */
    public function report(Request $req)
    {
        $mode = $req->get('view_mode', 'daily');

        if ($mode === 'monthly') {
            $monthRange = $this->resolveMonthlyDateRange($req);
            $baseQuery = $this->buildFilteredExpenseQuery($req, $monthRange['from'], $monthRange['to']);
            $summary = $this->calculateSummaryMetrics(clone $baseQuery, $monthRange['from'], $monthRange['to']);
            $rows = $this->aggregateMonthlyExpenses(clone $baseQuery);
        } else {
            $dates = $this->resolveDailyDateRange($req);
            $baseQuery = $this->buildFilteredExpenseQuery($req, $dates['from'], $dates['to']);
            $summary = $this->calculateSummaryMetrics(clone $baseQuery, $dates['from'], $dates['to']);
            $rows = $this->aggregateDailyExpenses(clone $baseQuery);
        }

        return response()->json([
            'status'    => 'success',
            'view_mode' => $mode,
            'summary'   => $summary,
            'rows'      => $rows,
        ]);
    }

    /**
     * AJAX endpoint to retrieve all itemized staff expense records for a period (e.g. '2026-08-30' or '2026-08')
     */
    public function details(Request $req, $period)
    {
        $query = StaffExpense::query()
            ->with([
                'staff:id,name,phone_number,email,image,position_id',
                'staff.position:id,name',
                'shop:id,name,phone,address',
                'createdBy:id,name,phone',
            ]);

        // Period filter: check if daily (YYYY-MM-DD) or monthly (YYYY-MM)
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $period)) {
            $query->whereDate('expense_date', $period);
        } elseif (preg_match('/^\d{4}-\d{2}$/', $period)) {
            $startOfMonth = Carbon::createFromFormat('Y-m', $period)->startOfMonth()->format('Y-m-d');
            $endOfMonth = Carbon::createFromFormat('Y-m', $period)->endOfMonth()->format('Y-m-d');
            $query->whereBetween('expense_date', [$startOfMonth, $endOfMonth]);
        }

        // Apply any active secondary filters
        $this->applySecondaryFilters($query, $req);

        $expenses = $query->orderBy('expense_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $formatted = $expenses->map(function ($item) {
            $typeEnum = $item->type instanceof ExpenseType ? $item->type : ExpenseType::tryFrom((string) $item->type);
            $typeValue = $typeEnum ? $typeEnum->value : (string) $item->type;
            $typeLabel = match($typeValue) {
                ExpenseType::SALARY->value    => __('staff_expense_report.types.salary'),
                ExpenseType::BONUS->value     => __('staff_expense_report.types.bonus'),
                ExpenseType::DEDUCTION->value => __('staff_expense_report.types.deduction'),
                default                       => __('staff_expense_report.types.other'),
            };
            $badgeClass = $typeEnum ? $typeEnum->badgeClass() : 'badge bg-secondary';
            $isDeduction = $typeEnum ? $typeEnum->isDeduction() : ($typeValue === 'Deduction');

            return [
                'id'                   => $item->id,
                'expense_date'         => $item->expense_date ? (is_object($item->expense_date) ? $item->expense_date->format('Y-m-d') : Carbon::parse($item->expense_date)->format('Y-m-d')) : '---',
                'expense_date_formatted' => $item->expense_date ? Carbon::parse($item->expense_date)->format('d M Y') : '---',
                'staff_id'             => $item->staff_id,
                'staff_name'           => $item->staff?->name ?: __('staff_expense_report.table.unknown_staff'),
                'staff_phone'          => $item->staff?->phone_number ?: '---',
                'staff_image'          => $item->staff?->image_url ?: null,
                'position_name'        => $item->staff?->position?->name ?: 'Staff',
                'shop_id'              => $item->shop_id,
                'shop_name'            => $item->shop?->name ?: __('staff_expense_report.filter.all_shops'),
                'type'                 => $typeValue,
                'type_label'           => $typeLabel,
                'badge_class'          => $badgeClass,
                'is_deduction'         => $isDeduction,
                'amount'               => (float) ($item->amount ?? 0),
                'amount_formatted'     => ($isDeduction ? '-$' : '+$') . number_format((float) ($item->amount ?? 0), 2),
                'description'          => $item->description ?: '---',
                'created_by_name'      => $item->createdBy?->name ?: __('staff_expense_report.table.system'),
                'status'               => (int) ($item->status ?? 1),
                'status_label'         => ((int) ($item->status ?? 1) === 1) ? __('staff_expense_report.status.active') : __('staff_expense_report.status.disabled'),
            ];
        });

        $salaryTotal    = (float) $formatted->where('type', ExpenseType::SALARY->value)->sum('amount');
        $bonusTotal     = (float) $formatted->where('type', ExpenseType::BONUS->value)->sum('amount');
        $deductionTotal = (float) $formatted->where('type', ExpenseType::DEDUCTION->value)->sum('amount');
        $otherTotal     = (float) $formatted->where('type', ExpenseType::OTHER->value)->sum('amount');
        $grossTotal     = $salaryTotal + $bonusTotal + $otherTotal;
        $netTotal       = $grossTotal - $deductionTotal;

        $isDailyPeriod = (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $period);
        $periodLabel = '';
        if ($isDailyPeriod) {
            $parsedDate = Carbon::parse($period);
            if (app()->getLocale() === 'km') {
                $dayKey = strtolower($parsedDate->format('D'));
                $periodLabel = 'ថ្ងៃ' . __('staff_expense_report.days.' . $dayKey) . ' ទី' . $parsedDate->format('d') . ' ' . __('staff_expense_report.months.' . $parsedDate->month) . ' ឆ្នាំ' . $parsedDate->format('Y');
            } else {
                $periodLabel = $parsedDate->format('l, d F Y');
            }
        } else {
            $parsedMonth = Carbon::createFromFormat('Y-m', $period);
            if (app()->getLocale() === 'km') {
                $periodLabel = __('staff_expense_report.months.' . $parsedMonth->month) . ' ឆ្នាំ' . $parsedMonth->format('Y');
            } else {
                $periodLabel = $parsedMonth->format('F Y');
            }
        }

        return response()->json([
            'status'          => 'success',
            'period'          => $period,
            'period_label'    => $periodLabel,
            'count'           => $formatted->count(),
            'salary_total'    => $salaryTotal,
            'bonus_total'     => $bonusTotal,
            'deduction_total' => $deductionTotal,
            'other_total'     => $otherTotal,
            'gross_total'     => $grossTotal,
            'net_total'       => $netTotal,
            'expenses'        => $formatted,
        ]);
    }

    /**
     * Build filtered base query on StaffExpense
     */
    private function buildFilteredExpenseQuery(Request $req, $fromDate, $toDate)
    {
        $query = StaffExpense::query()
            ->with(['staff', 'shop', 'createdBy']);

        if ($fromDate && $toDate) {
            $query->whereBetween('expense_date', [$fromDate, $toDate]);
        } elseif ($fromDate) {
            $query->whereDate('expense_date', '>=', $fromDate);
        } elseif ($toDate) {
            $query->whereDate('expense_date', '<=', $toDate);
        }

        $this->applySecondaryFilters($query, $req);

        return $query;
    }

    /**
     * Apply secondary filters (staff, shop, type, status, keyword search)
     */
    private function applySecondaryFilters($query, Request $req)
    {
        // Staff filter
        if ($req->filled('staff_id')) {
            $query->where('staff_id', $req->staff_id);
        }

        // Shop / Branch filter
        if ($req->filled('shop_id')) {
            $query->where('shop_id', $req->shop_id);
        }

        // Expense Type filter
        if ($req->filled('type') && $req->type !== 'all') {
            $query->where('type', $req->type);
        }

        // Status filter (Active by default unless specified)
        if ($req->filled('status')) {
            if ($req->status !== 'all') {
                $query->where('status', $req->status);
            }
        }

        // Keyword Search (Staff name, phone, description, amount)
        if ($req->filled('search')) {
            $search = trim($req->search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('staff', function ($s) use ($search) {
                    $s->where('name', 'like', "%{$search}%")
                      ->orWhere('phone_number', 'like', "%{$search}%");
                })
                ->orWhereHas('shop', function ($sh) use ($search) {
                    $sh->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('createdBy', function ($u) use ($search) {
                    $u->where('name', 'like', "%{$search}%");
                })
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('amount', 'like', "%{$search}%")
                ->orWhere('type', 'like', "%{$search}%");
            });
        }
    }

    /**
     * Calculate comprehensive summary metrics
     */
    private function calculateSummaryMetrics($query, $fromDate, $toDate)
    {
        $expenses = $query->get();

        $totalTransactions = $expenses->count();
        $salaryTotal = 0.0;
        $bonusTotal = 0.0;
        $deductionTotal = 0.0;
        $otherTotal = 0.0;
        $staffCounts = [];
        $typeCounts = [
            ExpenseType::SALARY->value    => 0,
            ExpenseType::BONUS->value     => 0,
            ExpenseType::DEDUCTION->value => 0,
            ExpenseType::OTHER->value     => 0,
        ];

        foreach ($expenses as $item) {
            $amt = (float) ($item->amount ?? 0);
            $type = $item->type instanceof ExpenseType ? $item->type->value : (string) $item->type;

            if ($item->staff_id) {
                $staffCounts[$item->staff_id] = ($staffCounts[$item->staff_id] ?? 0) + $amt;
            }

            if (isset($typeCounts[$type])) {
                $typeCounts[$type]++;
            }

            switch ($type) {
                case ExpenseType::SALARY->value:
                    $salaryTotal += $amt;
                    break;
                case ExpenseType::BONUS->value:
                    $bonusTotal += $amt;
                    break;
                case ExpenseType::DEDUCTION->value:
                    $deductionTotal += $amt;
                    break;
                default:
                    $otherTotal += $amt;
                    break;
            }
        }

        $grossTotal = $salaryTotal + $bonusTotal + $otherTotal;
        $netTotal   = $grossTotal - $deductionTotal;
        $distinctStaffCount = count($staffCounts);

        // Find top spending staff
        $topStaffId = null;
        $topStaffAmount = 0.0;
        if (!empty($staffCounts)) {
            arsort($staffCounts);
            $topStaffId = array_key_first($staffCounts);
            $topStaffAmount = $staffCounts[$topStaffId];
        }

        $topStaffName = $topStaffId ? Staff::find($topStaffId)?->name : __('staff_expense_report.table.na');
        $avgTransaction = $totalTransactions > 0 ? ($netTotal / $totalTransactions) : 0.0;

        return [
            'total_transactions'   => $totalTransactions,
            'salary_total'         => $salaryTotal,
            'bonus_total'          => $bonusTotal,
            'deduction_total'      => $deductionTotal,
            'other_total'          => $otherTotal,
            'gross_total'          => $grossTotal,
            'net_total'            => $netTotal,
            'distinct_staff_count' => $distinctStaffCount,
            'top_staff_name'       => $topStaffName,
            'top_staff_amount'     => $topStaffAmount,
            'avg_transaction'      => $avgTransaction,
            'type_counts'          => $typeCounts,
        ];
    }

    /**
     * Aggregate daily staff expenses grouped by DATE(expense_date)
     */
    private function aggregateDailyExpenses($query)
    {
        $expenses = $query->with(['staff', 'shop', 'createdBy'])
            ->orderBy('expense_date', 'desc')
            ->get();

        // Group by Date YYYY-MM-DD
        $grouped = $expenses->groupBy(function ($item) {
            return $item->expense_date ? (is_object($item->expense_date) ? $item->expense_date->format('Y-m-d') : Carbon::parse($item->expense_date)->format('Y-m-d')) : 'unknown';
        });

        $rows = [];
        $index = 1;

        foreach ($grouped as $dateKey => $dayExpenses) {
            if ($dateKey === 'unknown') continue;

            $cDate = Carbon::parse($dateKey);
            $transactionsCount = $dayExpenses->count();
            $salary = 0.0;
            $bonus = 0.0;
            $deduction = 0.0;
            $other = 0.0;
            $dayStaffAmounts = [];
            $shopCounts = [];

            foreach ($dayExpenses as $item) {
                $amt = (float) ($item->amount ?? 0);
                $type = $item->type instanceof ExpenseType ? $item->type->value : (string) $item->type;

                if ($item->staff_id) {
                    $staffName = $item->staff?->name ?: ('Staff #' . $item->staff_id);
                    $dayStaffAmounts[$staffName] = ($dayStaffAmounts[$staffName] ?? 0) + $amt;
                }

                if ($item->shop_id) {
                    $shopName = $item->shop?->name ?: 'HQ';
                    $shopCounts[$shopName] = ($shopCounts[$shopName] ?? 0) + 1;
                }

                switch ($type) {
                    case ExpenseType::SALARY->value:
                        $salary += $amt;
                        break;
                    case ExpenseType::BONUS->value:
                        $bonus += $amt;
                        break;
                    case ExpenseType::DEDUCTION->value:
                        $deduction += $amt;
                        break;
                    default:
                        $other += $amt;
                        break;
                }
            }

            $grossTotal = $salary + $bonus + $other;
            $netTotal   = $grossTotal - $deduction;

            // Top staff for this day
            $topStaff = '---';
            if (!empty($dayStaffAmounts)) {
                arsort($dayStaffAmounts);
                $topStaffName = array_key_first($dayStaffAmounts);
                $topStaff = $topStaffName . ' ($' . number_format($dayStaffAmounts[$topStaffName], 2) . ')';
            }

            // Top shop
            $topShop = __('staff_expense_report.filter.all_shops');
            if (!empty($shopCounts)) {
                arsort($shopCounts);
                $topShop = array_key_first($shopCounts);
            }

            $dayKey = strtolower($cDate->format('D'));
            $dayName = __('staff_expense_report.days.' . $dayKey);

            $rows[] = (object) [
                'index'              => $index++,
                'date'               => $dateKey,
                'date_formatted'     => $cDate->format('d M Y'),
                'day_key'            => $dayKey,
                'day_name'           => $dayName,
                'is_today'           => $cDate->isToday(),
                'is_weekend'         => $cDate->isWeekend(),
                'transactions_count' => $transactionsCount,
                'salary_total'       => $salary,
                'bonus_total'        => $bonus,
                'deduction_total'    => $deduction,
                'other_total'        => $other,
                'gross_total'        => $grossTotal,
                'net_total'          => $netTotal,
                'top_staff'          => $topStaff,
                'top_shop'           => $topShop,
            ];
        }

        return collect($rows);
    }

    /**
     * Aggregate monthly staff expenses grouped by DATE_FORMAT(expense_date, '%Y-%m')
     */
    private function aggregateMonthlyExpenses($query)
    {
        $expenses = $query->with(['staff', 'shop', 'createdBy'])
            ->orderBy('expense_date', 'desc')
            ->get();

        // Group by Month YYYY-MM
        $grouped = $expenses->groupBy(function ($item) {
            return $item->expense_date ? (is_object($item->expense_date) ? $item->expense_date->format('Y-m') : Carbon::parse($item->expense_date)->format('Y-m')) : 'unknown';
        });

        $rows = [];
        $index = 1;

        foreach ($grouped as $monthKey => $monthExpenses) {
            if ($monthKey === 'unknown') continue;

            $cMonth = Carbon::createFromFormat('Y-m', $monthKey);
            $transactionsCount = $monthExpenses->count();
            $salary = 0.0;
            $bonus = 0.0;
            $deduction = 0.0;
            $other = 0.0;
            $monthStaffAmounts = [];
            $shopCounts = [];
            $activeDays = [];

            foreach ($monthExpenses as $item) {
                $amt = (float) ($item->amount ?? 0);
                $type = $item->type instanceof ExpenseType ? $item->type->value : (string) $item->type;

                if ($item->expense_date) {
                    $dayStr = is_object($item->expense_date) ? $item->expense_date->format('Y-m-d') : Carbon::parse($item->expense_date)->format('Y-m-d');
                    $activeDays[$dayStr] = true;
                }

                if ($item->staff_id) {
                    $staffName = $item->staff?->name ?: ('Staff #' . $item->staff_id);
                    $monthStaffAmounts[$staffName] = ($monthStaffAmounts[$staffName] ?? 0) + $amt;
                }

                if ($item->shop_id) {
                    $shopName = $item->shop?->name ?: 'HQ';
                    $shopCounts[$shopName] = ($shopCounts[$shopName] ?? 0) + 1;
                }

                switch ($type) {
                    case ExpenseType::SALARY->value:
                        $salary += $amt;
                        break;
                    case ExpenseType::BONUS->value:
                        $bonus += $amt;
                        break;
                    case ExpenseType::DEDUCTION->value:
                        $deduction += $amt;
                        break;
                    default:
                        $other += $amt;
                        break;
                }
            }

            $grossTotal = $salary + $bonus + $other;
            $netTotal   = $grossTotal - $deduction;
            $activeDaysCount = count($activeDays);
            $avgDailyExpense = $activeDaysCount > 0 ? ($netTotal / $activeDaysCount) : 0.0;

            // Top staff for this month
            $topStaff = '---';
            if (!empty($monthStaffAmounts)) {
                arsort($monthStaffAmounts);
                $topStaffName = array_key_first($monthStaffAmounts);
                $topStaff = $topStaffName . ' ($' . number_format($monthStaffAmounts[$topStaffName], 2) . ')';
            }

            // Top shop
            $topShop = __('staff_expense_report.filter.all_shops');
            if (!empty($shopCounts)) {
                arsort($shopCounts);
                $topShop = array_key_first($shopCounts);
            }

            $monthName = app()->getLocale() === 'km'
                ? (__('staff_expense_report.months.' . (int) $cMonth->month) . ' ' . $cMonth->year)
                : $cMonth->format('F Y');

            $rows[] = (object) [
                'index'              => $index++,
                'month_key'          => $monthKey,
                'month_name'         => $monthName,
                'short_month'        => $cMonth->format('M Y'),
                'year'               => $cMonth->format('Y'),
                'active_days'        => $activeDaysCount,
                'transactions_count' => $transactionsCount,
                'salary_total'       => $salary,
                'bonus_total'        => $bonus,
                'deduction_total'    => $deduction,
                'other_total'        => $other,
                'gross_total'        => $grossTotal,
                'net_total'          => $netTotal,
                'avg_daily_expense'  => $avgDailyExpense,
                'top_staff'          => $topStaff,
                'top_shop'           => $topShop,
            ];
        }

        return collect($rows);
    }

    /**
     * Resolve date range for Daily view
     */
    private function resolveDailyDateRange(Request $req)
    {
        $now = Carbon::now();
        $preset = $req->get('preset');

        if ($preset === 'today') {
            return [
                'from' => $now->format('Y-m-d'),
                'to'   => $now->format('Y-m-d'),
            ];
        }

        if ($preset === 'yesterday') {
            $yesterday = $now->copy()->subDay()->format('Y-m-d');
            return [
                'from' => $yesterday,
                'to'   => $yesterday,
            ];
        }

        if ($preset === '7days') {
            return [
                'from' => $now->copy()->subDays(6)->format('Y-m-d'),
                'to'   => $now->format('Y-m-d'),
            ];
        }

        if ($preset === '30days') {
            return [
                'from' => $now->copy()->subDays(29)->format('Y-m-d'),
                'to'   => $now->format('Y-m-d'),
            ];
        }

        if ($preset === 'this_month') {
            return [
                'from' => $now->copy()->startOfMonth()->format('Y-m-d'),
                'to'   => $now->format('Y-m-d'),
            ];
        }

        if ($preset === 'last_month') {
            $lastMonth = $now->copy()->subMonth();
            return [
                'from' => $lastMonth->copy()->startOfMonth()->format('Y-m-d'),
                'to'   => $lastMonth->copy()->endOfMonth()->format('Y-m-d'),
            ];
        }

        // Custom date range or default to current month
        $from = $req->filled('from_date') ? $req->from_date : $now->copy()->startOfMonth()->format('Y-m-d');
        $to   = $req->filled('to_date')   ? $req->to_date   : $now->copy()->endOfMonth()->format('Y-m-d');

        return [
            'from' => $from,
            'to'   => $to,
        ];
    }

    /**
     * Resolve date range for Monthly view
     */
    private function resolveMonthlyDateRange(Request $req)
    {
        $now = Carbon::now();
        $selectedYear = (int) ($req->filled('year') ? $req->year : $now->year);

        $fromMonth = (int) ($req->filled('from_month') ? $req->from_month : 1);
        $toMonth   = (int) ($req->filled('to_month')   ? $req->to_month   : 12);

        $fromMonth = max(1, min(12, $fromMonth));
        $toMonth   = max($fromMonth, min(12, $toMonth));

        $fromDate = Carbon::create($selectedYear, $fromMonth, 1)->startOfMonth()->format('Y-m-d');
        $toDate   = Carbon::create($selectedYear, $toMonth, 1)->endOfMonth()->format('Y-m-d');

        return [
            'year'       => $selectedYear,
            'from_month' => $fromMonth,
            'to_month'   => $toMonth,
            'from'       => $fromDate,
            'to'         => $toDate,
        ];
    }

    /**
     * Extract active filter values from request
     */
    private function extractFilters(Request $req, $mode)
    {
        return [
            'staff_id' => $req->get('staff_id', ''),
            'shop_id'  => $req->get('shop_id', ''),
            'type'     => $req->get('type', 'all'),
            'status'   => $req->get('status', '1'),
            'search'   => $req->get('search', ''),
            'preset'   => $req->get('preset', ''),
        ];
    }

    /**
     * Get lookup options for filter dropdowns
     */
    private function getFilterOptions()
    {
        $staffList = Staff::where('status', 1)->orderBy('name', 'asc')->get(['id', 'name', 'phone_number']);
        $shops     = Shop::where('status', 1)->orderBy('name', 'asc')->get(['id', 'name']);
        $expenseTypes = ExpenseType::cases();

        return [
            'staffList'    => $staffList,
            'shops'        => $shops,
            'expenseTypes' => $expenseTypes,
        ];
    }

    /**
     * Get list of available years for monthly dropdown
     */
    private function getAvailableYears()
    {
        $currentYear = (int) Carbon::now()->year;
        $dbYears = StaffExpense::select(DB::raw('YEAR(expense_date) as yr'))
            ->whereNotNull('expense_date')
            ->groupBy('yr')
            ->pluck('yr')
            ->map(fn($y) => (int) $y)
            ->toArray();

        $years = array_unique(array_merge([$currentYear, $currentYear - 1, $currentYear - 2], $dbYears));
        rsort($years);

        return $years;
    }
}
