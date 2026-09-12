<?php

namespace App\Http\Controllers\Admin\Report;

use App\Http\Controllers\Controller;
use App\Models\Barber;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\Customer;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderTransactionReportController extends Controller
{
    protected $layout = 'admin::pages.report.orderTransaction.';
    private $routeName = 'report-order-transaction';

    public function __construct()
    {
        $this->middleware('permission:report-transaction-view|report-sales-view|booking-view', [
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
     * Daily Order Transaction Report View
     */
    public function daily(Request $req)
    {
        $dates = $this->resolveDailyDateRange($req);
        $appliedFilters = $this->extractFilters($req, 'daily');

        $baseQuery = $this->buildFilteredBookingQuery($req, $dates['from'], $dates['to']);

        // Overall summary statistics for filtered date range
        $summary = $this->calculateSummaryMetrics(clone $baseQuery, $dates['from'], $dates['to']);

        // Daily aggregated breakdown
        $dailyRows = $this->aggregateDailyTransactions(clone $baseQuery);

        $filterOptions = $this->getFilterOptions();

        return view($this->layout . 'index', [
            'viewMode' => 'daily',
            'routeName' => $this->routeName,
            'from_date' => $dates['from'],
            'to_date' => $dates['to'],
            'filters' => $appliedFilters,
            'summary' => $summary,
            'rows' => $dailyRows,
            'shops' => $filterOptions['shops'],
            'barbers' => $filterOptions['barbers'],
            'paymentMethods' => $filterOptions['paymentMethods'],
            'paymentStatuses' => $filterOptions['paymentStatuses'],
        ]);
    }

    /**
     * Monthly Order Transaction Report View
     */
    public function monthly(Request $req)
    {
        $monthRange = $this->resolveMonthlyDateRange($req);
        $appliedFilters = $this->extractFilters($req, 'monthly');

        $baseQuery = $this->buildFilteredBookingQuery($req, $monthRange['from'], $monthRange['to']);

        // Overall summary statistics for filtered monthly range
        $summary = $this->calculateSummaryMetrics(clone $baseQuery, $monthRange['from'], $monthRange['to']);

        // Monthly aggregated breakdown
        $monthlyRows = $this->aggregateMonthlyTransactions(clone $baseQuery);

        $filterOptions = $this->getFilterOptions();

        return view($this->layout . 'index', [
            'viewMode' => 'monthly',
            'routeName' => $this->routeName,
            'selectedYear' => $monthRange['year'],
            'from_month' => $monthRange['from_month'],
            'to_month' => $monthRange['to_month'],
            'from_date' => $monthRange['from'],
            'to_date' => $monthRange['to'],
            'filters' => $appliedFilters,
            'summary' => $summary,
            'rows' => $monthlyRows,
            'shops' => $filterOptions['shops'],
            'barbers' => $filterOptions['barbers'],
            'paymentMethods' => $filterOptions['paymentMethods'],
            'paymentStatuses' => $filterOptions['paymentStatuses'],
            'availableYears' => $this->getAvailableYears(),
        ]);
    }

    /**
     * API / JSON endpoint for export and charts
     */
    public function report(Request $req)
    {
        $mode = $req->get('view_mode', 'daily');

        if ($mode === 'monthly') {
            $monthRange = $this->resolveMonthlyDateRange($req);
            $baseQuery = $this->buildFilteredBookingQuery($req, $monthRange['from'], $monthRange['to']);
            $summary = $this->calculateSummaryMetrics(clone $baseQuery, $monthRange['from'], $monthRange['to']);
            $rows = $this->aggregateMonthlyTransactions(clone $baseQuery);
        } else {
            $dates = $this->resolveDailyDateRange($req);
            $baseQuery = $this->buildFilteredBookingQuery($req, $dates['from'], $dates['to']);
            $summary = $this->calculateSummaryMetrics(clone $baseQuery, $dates['from'], $dates['to']);
            $rows = $this->aggregateDailyTransactions(clone $baseQuery);
        }

        return response()->json([
            'status' => 'success',
            'view_mode' => $mode,
            'summary' => $summary,
            'rows' => $rows,
        ]);
    }

    /**
     * AJAX endpoint to retrieve all order records for a period (e.g. '2026-08-30' or '2026-08')
     */
    public function details(Request $req, $period)
    {
        $query = Booking::query()
            ->with([
                'shop:id,name,phone,address',
                'barber:id,name,phone',
                'customer:id,name,phone',
                'bookingDetail' => function ($detail) {
                    $detail->withTrashed()->with(['service:id,name', 'product:id,name']);
                },
                'payments:id,booking_id,amount,payment_method,payment_date',
            ]);

        // Period filter: check if daily (YYYY-MM-DD) or monthly (YYYY-MM)
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $period)) {
            $query->whereDate('booking_date', $period);
        } elseif (preg_match('/^\d{4}-\d{2}$/', $period)) {
            $startOfMonth = Carbon::createFromFormat('Y-m', $period)->startOfMonth()->format('Y-m-d');
            $endOfMonth = Carbon::createFromFormat('Y-m', $period)->endOfMonth()->format('Y-m-d');
            $query->whereBetween(DB::raw('DATE(booking_date)'), [$startOfMonth, $endOfMonth]);
        }

        // Apply any active secondary filters
        $this->applySecondaryFilters($query, $req);

        $bookings = $query->orderBy('booking_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $formatted = $bookings->map(function ($booking) {
            $items = $booking->bookingDetail->map(function ($detail) {
                $isService = $detail->type === 'service';
                $name = $isService ? ($detail->service?->name ?? __('order_transaction.modal.unknown_service')) : ($detail->product?->name ?? __('order_transaction.modal.unknown_product'));
                return [
                    'id' => $detail->id,
                    'type' => $detail->type ?: ($isService ? 'service' : 'product'),
                    'name' => $name,
                    'qty' => (int) ($detail->qty ?: 1),
                    'price' => (float) ($detail->price ?? 0),
                    'discount' => (float) ($isService ? ($detail->service_discount ?? 0) : ($detail->product_discount ?? 0)),
                    'total' => (float) (($detail->price ?? 0) * ($detail->qty ?: 1)),
                ];
            });

            return [
                'id' => $booking->id,
                'invoice_number' => $booking->invoice_number ?: ('#' . $booking->id),
                'booking_date' => $booking->booking_date ? Carbon::parse($booking->booking_date)->format('Y-m-d H:i') : '---',
                'booking_date_formatted' => $booking->booking_date ? Carbon::parse($booking->booking_date)->format('d M Y, h:i A') : '---',
                'shop_name' => $booking->shop?->name ?: '---',
                'barber_name' => $booking->barber?->name ?: '---',
                'customer_name' => $booking->customer?->name ?: __('order_transaction.modal.walk_in_customer'),
                'customer_phone' => $booking->customer?->phone ?: '---',
                'payment_status' => $booking->payment_status ?: 'Pending',
                'pay_way' => $booking->pay_way ?: ($booking->payments->first()?->payment_method ?: 'Cash'),
                'total_price' => (float) ($booking->total_price ?? 0),
                'total_discount' => (float) ($booking->total_discount ?? 0),
                'paid_amount' => (float) ($booking->paid_amount ?? 0),
                'remaining_amount' => (float) ($booking->remaining_amount ?? 0),
                'items' => $items,
                'items_count' => $items->sum('qty'),
                'remark' => $booking->remark,
            ];
        });

        $totalRevenue = $formatted->sum('total_price');
        $totalPaid = $formatted->sum('paid_amount');
        $totalRemaining = $formatted->sum('remaining_amount');

        $isDailyPeriod = (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $period);
        $periodLabel = '';
        if ($isDailyPeriod) {
            $parsedDate = Carbon::parse($period);
            if (app()->getLocale() === 'km') {
                $dayKey = strtolower($parsedDate->format('D'));
                $periodLabel = 'ថ្ងៃ' . __('order_transaction.days.' . $dayKey) . ' ទី' . $parsedDate->format('d') . ' ' . __('order_transaction.months.' . $parsedDate->month) . ' ឆ្នាំ' . $parsedDate->format('Y');
            } else {
                $periodLabel = $parsedDate->format('l, d F Y');
            }
        } else {
            $parsedMonth = Carbon::createFromFormat('Y-m', $period);
            if (app()->getLocale() === 'km') {
                $periodLabel = __('order_transaction.months.' . $parsedMonth->month) . ' ឆ្នាំ' . $parsedMonth->format('Y');
            } else {
                $periodLabel = $parsedMonth->format('F Y');
            }
        }

        return response()->json([
            'status' => 'success',
            'period' => $period,
            'period_label' => $periodLabel,
            'count' => $formatted->count(),
            'total_revenue' => $totalRevenue,
            'total_paid' => $totalPaid,
            'total_remaining' => $totalRemaining,
            'bookings' => $formatted,
        ]);
    }

    /**
     * Build filtered base query on Booking
     */
    private function buildFilteredBookingQuery(Request $req, $fromDate, $toDate)
    {
        $query = Booking::query()
            ->with(['shop', 'barber', 'customer', 'bookingDetail']);

        if ($fromDate && $toDate) {
            $query->whereBetween(DB::raw('DATE(booking_date)'), [$fromDate, $toDate]);
        } elseif ($fromDate) {
            $query->whereDate('booking_date', '>=', $fromDate);
        } elseif ($toDate) {
            $query->whereDate('booking_date', '<=', $toDate);
        }

        $this->applySecondaryFilters($query, $req);

        return $query;
    }

    /**
     * Apply secondary filters (shop, barber, customer, payment status, payment method, item type, keyword)
     */
    private function applySecondaryFilters($query, Request $req)
    {
        // Shop filter
        if ($req->filled('shop_id')) {
            $query->where('shop_id', $req->shop_id);
        }

        // Barber / Staff filter
        if ($req->filled('barber_id')) {
            $query->where('barber_id', $req->barber_id);
        }

        // Customer filter
        if ($req->filled('customer_id')) {
            $query->where('customer_id', $req->customer_id);
        }

        // Payment status filter
        if ($req->filled('payment_status')) {
            $status = $req->payment_status;
            if ($status !== 'all') {
                $query->where('payment_status', $status);
            }
        } else {
            // By default, exclude canceled bookings unless explicitly requested
            $query->where('payment_status', '!=', 'Cancel');
        }

        // Payment method / Pay way filter
        if ($req->filled('pay_way') && $req->pay_way !== 'all') {
            $query->where(function ($q) use ($req) {
                $q->where('pay_way', $req->pay_way)
                    ->orWhereHas('payments', function ($p) use ($req) {
                        $p->where('payment_method', $req->pay_way);
                    });
            });
        }

        // Item type filter (Product only / Service only)
        if ($req->filled('item_type') && in_array($req->item_type, ['product', 'service'])) {
            $query->whereHas('bookingDetail', function ($detail) use ($req) {
                $detail->where('type', $req->item_type);
            });
        }

        // Keyword Search
        if ($req->filled('search')) {
            $search = trim($req->search);
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($c) use ($search) {
                        $c->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })
                    ->orWhereHas('shop', function ($s) use ($search) {
                        $s->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('barber', function ($b) use ($search) {
                        $b->where('name', 'like', "%{$search}%");
                    });
            });
        }
    }

    /**
     * Calculate comprehensive summary metrics
     */
    private function calculateSummaryMetrics($query, $fromDate, $toDate)
    {
        $bookings = $query->get();

        $totalInvoices = $bookings->count();
        $totalNetSales = (float) $bookings->sum('total_price');
        $totalDiscount = (float) $bookings->sum('total_discount');
        $totalGrossSales = $totalNetSales + $totalDiscount;
        $totalPaid = (float) $bookings->sum('paid_amount');
        $totalRemaining = max(0, $totalNetSales - $totalPaid);

        // Calculate product vs service breakdown and item quantities
        $bookingIds = $bookings->pluck('id')->toArray();
        $productSales = 0.0;
        $serviceSales = 0.0;
        $totalProductQty = 0;
        $totalServiceQty = 0;

        if (!empty($bookingIds)) {
            $details = BookingDetail::withTrashed()
                ->whereIn('booking_id', $bookingIds)
                ->get();

            foreach ($details as $detail) {
                $qty = (int) ($detail->qty ?: 1);
                $price = (float) ($detail->price ?? 0);
                $lineTotal = $price * $qty;

                if ($detail->type === 'product' || ($detail->product_id && !$detail->service_id)) {
                    $disc = (float) ($detail->product_discount ?? 0);
                    $productSales += max(0, $lineTotal - $disc);
                    $totalProductQty += $qty;
                } else {
                    $disc = (float) ($detail->service_discount ?? 0);
                    $serviceSales += max(0, $lineTotal - $disc);
                    $totalServiceQty += $qty;
                }
            }
        }

        $totalItemsSold = $totalProductQty + $totalServiceQty;
        $avgInvoiceValue = $totalInvoices > 0 ? ($totalNetSales / $totalInvoices) : 0;

        // Payment status counts
        $paidCount = $bookings->where('payment_status', 'Paid')->count();
        $partialCount = $bookings->where('payment_status', 'Partial')->count();
        $pendingCount = $bookings->where('payment_status', 'Pending')->count();

        return [
            'total_invoices' => $totalInvoices,
            'total_gross_sales' => $totalGrossSales,
            'total_discount' => $totalDiscount,
            'total_net_sales' => $totalNetSales,
            'total_paid' => $totalPaid,
            'total_remaining' => $totalRemaining,
            'product_sales' => $productSales,
            'service_sales' => $serviceSales,
            'total_product_qty' => $totalProductQty,
            'total_service_qty' => $totalServiceQty,
            'total_items_sold' => $totalItemsSold,
            'avg_invoice_value' => $avgInvoiceValue,
            'paid_count' => $paidCount,
            'partial_count' => $partialCount,
            'pending_count' => $pendingCount,
        ];
    }

    /**
     * Aggregate daily transactions grouped by DATE(booking_date)
     */
    private function aggregateDailyTransactions($query)
    {
        $bookings = $query->with([
            'bookingDetail' => function ($d) {
                $d->withTrashed();
            },
            'payments'
        ])
        ->orderBy('booking_date', 'desc')
        ->get();

        // Group by Date YYYY-MM-DD
        $grouped = $bookings->groupBy(function ($item) {
            return $item->booking_date ? Carbon::parse($item->booking_date)->format('Y-m-d') : 'unknown';
        });

        $rows = [];
        $index = 1;

        foreach ($grouped as $dateKey => $dayBookings) {
            if ($dateKey === 'unknown') continue;

            $cDate = Carbon::parse($dateKey);
            $invoicesCount = $dayBookings->count();
            $netSales = (float) $dayBookings->sum('total_price');
            $discount = (float) $dayBookings->sum('total_discount');
            $grossSales = $netSales + $discount;
            $paid = (float) $dayBookings->sum('paid_amount');
            $remaining = max(0, $netSales - $paid);

            // Item count & breakdown for this day
            $itemsQty = 0;
            $productRevenue = 0.0;
            $serviceRevenue = 0.0;

            foreach ($dayBookings as $b) {
                foreach ($b->bookingDetail as $d) {
                    $qty = (int) ($d->qty ?: 1);
                    $price = (float) ($d->price ?? 0);
                    $itemsQty += $qty;

                    if ($d->type === 'product' || ($d->product_id && !$d->service_id)) {
                        $productRevenue += max(0, ($price * $qty) - (float) ($d->product_discount ?? 0));
                    } else {
                        $serviceRevenue += max(0, ($price * $qty) - (float) ($d->service_discount ?? 0));
                    }
                }
            }

            // Payment method breakdown
            $payMethods = [];
            foreach ($dayBookings as $b) {
                $method = $b->pay_way ?: ($b->payments->first()?->payment_method ?: 'Cash');
                $method = trim($method) ?: 'Cash';
                if (!isset($payMethods[$method])) {
                    $payMethods[$method] = 0;
                }
                $payMethods[$method]++;
            }

            // Status counts
            $paidCount = $dayBookings->where('payment_status', 'Paid')->count();
            $partialCount = $dayBookings->where('payment_status', 'Partial')->count();
            $pendingCount = $dayBookings->where('payment_status', 'Pending')->count();

            $rows[] = (object) [
                'index' => $index++,
                'date' => $dateKey,
                'date_formatted' => $cDate->format('d M Y'),
                'day_name' => $cDate->format('D'),
                'is_today' => $cDate->isToday(),
                'is_weekend' => $cDate->isWeekend(),
                'invoices_count' => $invoicesCount,
                'items_qty' => $itemsQty,
                'gross_sales' => $grossSales,
                'discount' => $discount,
                'net_sales' => $netSales,
                'paid_amount' => $paid,
                'remaining_amount' => $remaining,
                'product_revenue' => $productRevenue,
                'service_revenue' => $serviceRevenue,
                'paid_count' => $paidCount,
                'partial_count' => $partialCount,
                'pending_count' => $pendingCount,
                'payment_methods' => $payMethods,
                'avg_ticket' => $invoicesCount > 0 ? ($netSales / $invoicesCount) : 0,
            ];
        }

        return collect($rows);
    }

    /**
     * Aggregate monthly transactions grouped by DATE_FORMAT(booking_date, '%Y-%m')
     */
    private function aggregateMonthlyTransactions($query)
    {
        $bookings = $query->with([
            'shop',
            'barber',
            'bookingDetail' => function ($d) {
                $d->withTrashed();
            },
            'payments'
        ])
        ->orderBy('booking_date', 'desc')
        ->get();

        // Group by Month YYYY-MM
        $grouped = $bookings->groupBy(function ($item) {
            return $item->booking_date ? Carbon::parse($item->booking_date)->format('Y-m') : 'unknown';
        });

        $rows = [];
        $index = 1;

        foreach ($grouped as $monthKey => $monthBookings) {
            if ($monthKey === 'unknown') continue;

            $cMonth = Carbon::createFromFormat('Y-m', $monthKey);
            $invoicesCount = $monthBookings->count();
            $netSales = (float) $monthBookings->sum('total_price');
            $discount = (float) $monthBookings->sum('total_discount');
            $grossSales = $netSales + $discount;
            $paid = (float) $monthBookings->sum('paid_amount');
            $remaining = max(0, $netSales - $paid);

            // Active sales days in this month
            $activeDays = $monthBookings->pluck('booking_date')
                ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
                ->unique()
                ->count();

            // Total items and revenue breakdown
            $itemsQty = 0;
            $productRevenue = 0.0;
            $serviceRevenue = 0.0;

            foreach ($monthBookings as $b) {
                foreach ($b->bookingDetail as $d) {
                    $qty = (int) ($d->qty ?: 1);
                    $price = (float) ($d->price ?? 0);
                    $itemsQty += $qty;

                    if ($d->type === 'product' || ($d->product_id && !$d->service_id)) {
                        $productRevenue += max(0, ($price * $qty) - (float) ($d->product_discount ?? 0));
                    } else {
                        $serviceRevenue += max(0, ($price * $qty) - (float) ($d->service_discount ?? 0));
                    }
                }
            }

            // Top Payment method in month
            $payMethods = [];
            foreach ($monthBookings as $b) {
                $method = $b->pay_way ?: ($b->payments->first()?->payment_method ?: 'Cash');
                $method = trim($method) ?: 'Cash';
                $payMethods[$method] = ($payMethods[$method] ?? 0) + 1;
            }
            arsort($payMethods);
            $topPayMethod = !empty($payMethods) ? array_key_first($payMethods) : '---';

            // Top shop
            $shopCounts = $monthBookings->whereNotNull('shop_id')->groupBy('shop_id')->map->count()->toArray();
            arsort($shopCounts);
            $topShopId = !empty($shopCounts) ? array_key_first($shopCounts) : null;
            $topShopName = $topShopId ? ($monthBookings->firstWhere('shop_id', $topShopId)?->shop?->name ?? '---') : '---';

            $rows[] = (object) [
                'index' => $index++,
                'month_key' => $monthKey,
                'month_name' => $cMonth->format('F Y'),
                'short_month' => $cMonth->format('M Y'),
                'active_days' => $activeDays,
                'invoices_count' => $invoicesCount,
                'items_qty' => $itemsQty,
                'gross_sales' => $grossSales,
                'discount' => $discount,
                'net_sales' => $netSales,
                'paid_amount' => $paid,
                'remaining_amount' => $remaining,
                'product_revenue' => $productRevenue,
                'service_revenue' => $serviceRevenue,
                'avg_ticket' => $invoicesCount > 0 ? ($netSales / $invoicesCount) : 0,
                'avg_daily_sales' => $activeDays > 0 ? ($netSales / $activeDays) : 0,
                'top_pay_method' => $topPayMethod,
                'top_shop_name' => $topShopName,
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

        // Check for preset quick filters
        $preset = $req->get('preset');
        if ($preset === 'today') {
            return [
                'from' => $now->format('Y-m-d'),
                'to' => $now->format('Y-m-d'),
            ];
        } elseif ($preset === 'yesterday') {
            $yesterday = $now->copy()->subDay()->format('Y-m-d');
            return [
                'from' => $yesterday,
                'to' => $yesterday,
            ];
        } elseif ($preset === '7days') {
            return [
                'from' => $now->copy()->subDays(6)->format('Y-m-d'),
                'to' => $now->format('Y-m-d'),
            ];
        } elseif ($preset === '30days') {
            return [
                'from' => $now->copy()->subDays(29)->format('Y-m-d'),
                'to' => $now->format('Y-m-d'),
            ];
        } elseif ($preset === 'last_month') {
            $lastMonth = $now->copy()->subMonth();
            return [
                'from' => $lastMonth->copy()->startOfMonth()->format('Y-m-d'),
                'to' => $lastMonth->copy()->endOfMonth()->format('Y-m-d'),
            ];
        }

        // Custom date range or default to current month
        $from = $req->filled('from_date') ? $req->from_date : $now->copy()->startOfMonth()->format('Y-m-d');
        $to = $req->filled('to_date') ? $req->to_date : $now->copy()->endOfMonth()->format('Y-m-d');

        return [
            'from' => $from,
            'to' => $to,
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
        $toMonth = (int) ($req->filled('to_month') ? $req->to_month : 12);

        $fromMonth = max(1, min(12, $fromMonth));
        $toMonth = max($fromMonth, min(12, $toMonth));

        $fromDate = Carbon::create($selectedYear, $fromMonth, 1)->startOfMonth()->format('Y-m-d');
        $toDate = Carbon::create($selectedYear, $toMonth, 1)->endOfMonth()->format('Y-m-d');

        return [
            'year' => $selectedYear,
            'from_month' => $fromMonth,
            'to_month' => $toMonth,
            'from' => $fromDate,
            'to' => $toDate,
        ];
    }

    /**
     * Extract active filter values from request
     */
    private function extractFilters(Request $req, $mode)
    {
        return [
            'shop_id' => $req->get('shop_id', ''),
            'barber_id' => $req->get('barber_id', ''),
            'customer_id' => $req->get('customer_id', ''),
            'payment_status' => $req->get('payment_status', ''),
            'pay_way' => $req->get('pay_way', ''),
            'item_type' => $req->get('item_type', ''),
            'search' => $req->get('search', ''),
            'preset' => $req->get('preset', ''),
        ];
    }

    /**
     * Get filter options for dropdowns
     */
    private function getFilterOptions()
    {
        $shops = Shop::where('status', 1)->orderBy('name', 'asc')->get(['id', 'name']);
        $barbers = Barber::where('status', 1)->orderBy('name', 'asc')->get(['id', 'name']);

        $paymentMethods = ['Cash', 'ABA PAY', 'KHQR', 'Credit Card', 'Bank Transfer'];
        $paymentStatuses = ['Paid', 'Partial', 'Pending', 'Cancel'];

        return [
            'shops' => $shops,
            'barbers' => $barbers,
            'paymentMethods' => $paymentMethods,
            'paymentStatuses' => $paymentStatuses,
        ];
    }

    /**
     * Get list of available years for monthly dropdown
     */
    private function getAvailableYears()
    {
        $currentYear = (int) Carbon::now()->year;
        $oldestYear = $currentYear - 4;

        $dbMinYear = Booking::min(DB::raw('YEAR(booking_date)'));
        if ($dbMinYear && $dbMinYear < $oldestYear) {
            $oldestYear = (int) $dbMinYear;
        }

        $years = [];
        for ($y = $currentYear + 1; $y >= $oldestYear; $y--) {
            $years[] = $y;
        }

        return $years;
    }
}
