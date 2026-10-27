<?php

namespace App\Http\Controllers\Admin\Report;

use App\Http\Controllers\Controller;
use App\Models\OtherIncome;
use App\Models\OtherIncomeCategory;
use App\Models\Shop;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OtherIncomeReportController extends Controller
{
    protected $layout = 'admin::pages.report.otherIncome.';
    private $routeName = 'report-other-income';

    public function __construct()
    {
        $this->middleware('permission:report-other-income-view', [
            'only' => ['index', 'daily', 'monthly', 'report', 'details']
        ]);
    }

    /**
     * Resolve grouping parameter ('grouped' or 'ungrouped', default 'ungrouped')
     */
    private function resolveGrouping(Request $req): string
    {
        $grouping = $req->get('grouping', 'ungrouped');
        return in_array($grouping, ['grouped', 'ungrouped'], true) ? $grouping : 'ungrouped';
    }

    /**
     * Entry point: Default to Daily view
     */
    public function index(Request $req)
    {
        return redirect()->route('admin-' . $this->routeName . '-daily', $req->query());
    }

    /**
     * Daily Other Income Report View
     */
    public function daily(Request $req)
    {
        $grouping = $this->resolveGrouping($req);
        $dates = $this->resolveDailyDateRange($req);
        $appliedFilters = $this->extractFilters($req, 'daily');

        $baseQuery = $this->buildFilteredIncomeQuery($req, $dates['from'], $dates['to']);

        $summary = $this->calculateSummaryMetrics(clone $baseQuery);

        $rows = null;
        $ungroupedRows = null;

        if ($grouping === 'grouped') {
            $rows = $this->aggregateDailyIncomes(clone $baseQuery);
        } else {
            $ungroupedRows = $this->getUngroupedPaginated(clone $baseQuery, $req);
        }

        $filterOptions = $this->getFilterOptions();

        return view($this->layout . 'index', [
            'viewMode'      => 'daily',
            'grouping'      => $grouping,
            'routeName'     => $this->routeName,
            'from_date'     => $dates['from'],
            'to_date'       => $dates['to'],
            'filters'       => $appliedFilters,
            'summary'       => $summary,
            'rows'          => $rows,
            'ungroupedRows' => $ungroupedRows,
            'categories'    => $filterOptions['categories'],
            'suppliers'     => $filterOptions['suppliers'],
            'shops'         => $filterOptions['shops'],
        ]);
    }

    /**
     * Monthly Other Income Report View
     */
    public function monthly(Request $req)
    {
        $grouping = $this->resolveGrouping($req);
        $monthRange = $this->resolveMonthlyDateRange($req);
        $appliedFilters = $this->extractFilters($req, 'monthly');

        $baseQuery = $this->buildFilteredIncomeQuery($req, $monthRange['from'], $monthRange['to']);

        $summary = $this->calculateSummaryMetrics(clone $baseQuery);

        $rows = null;
        $ungroupedRows = null;

        if ($grouping === 'grouped') {
            $rows = $this->aggregateMonthlyIncomes(clone $baseQuery);
        } else {
            $ungroupedRows = $this->getUngroupedPaginated(clone $baseQuery, $req);
        }

        $filterOptions = $this->getFilterOptions();

        return view($this->layout . 'index', [
            'viewMode'       => 'monthly',
            'grouping'       => $grouping,
            'routeName'      => $this->routeName,
            'selectedYear'   => $monthRange['year'],
            'from_month'     => $monthRange['from_month'],
            'to_month'       => $monthRange['to_month'],
            'from_date'      => $monthRange['from'],
            'to_date'        => $monthRange['to'],
            'filters'        => $appliedFilters,
            'summary'        => $summary,
            'rows'           => $rows,
            'ungroupedRows'  => $ungroupedRows,
            'categories'     => $filterOptions['categories'],
            'suppliers'      => $filterOptions['suppliers'],
            'shops'          => $filterOptions['shops'],
            'availableYears' => $this->getAvailableYears(),
        ]);
    }

    /**
     * API / JSON endpoint for ExcelJS export
     */
    public function report(Request $req)
    {
        $mode = $req->get('view_mode', 'daily');
        $grouping = $this->resolveGrouping($req);

        if ($mode === 'monthly') {
            $monthRange = $this->resolveMonthlyDateRange($req);
            $baseQuery = $this->buildFilteredIncomeQuery($req, $monthRange['from'], $monthRange['to']);
            $summary = $this->calculateSummaryMetrics(clone $baseQuery);
            $rows = ($grouping === 'ungrouped')
                ? $this->getUngroupedAll(clone $baseQuery)
                : $this->aggregateMonthlyIncomes(clone $baseQuery);
        } else {
            $dates = $this->resolveDailyDateRange($req);
            $baseQuery = $this->buildFilteredIncomeQuery($req, $dates['from'], $dates['to']);
            $summary = $this->calculateSummaryMetrics(clone $baseQuery);
            $rows = ($grouping === 'ungrouped')
                ? $this->getUngroupedAll(clone $baseQuery)
                : $this->aggregateDailyIncomes(clone $baseQuery);
        }

        return response()->json([
            'status'    => 'success',
            'view_mode' => $mode,
            'grouping'  => $grouping,
            'summary'   => $summary,
            'rows'      => $rows,
        ]);
    }

    /**
     * AJAX endpoint for period details modal
     */
    public function details(Request $req, $period)
    {
        $query = OtherIncome::query()
            ->with(['supplier', 'category', 'shop', 'createdBy', 'payments']);

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $period)) {
            $query->whereDate('income_date', $period);
        } elseif (preg_match('/^\d{4}-\d{2}$/', $period)) {
            $startOfMonth = Carbon::createFromFormat('Y-m', $period)->startOfMonth()->format('Y-m-d');
            $endOfMonth = Carbon::createFromFormat('Y-m', $period)->endOfMonth()->format('Y-m-d');
            $query->whereBetween('income_date', [$startOfMonth, $endOfMonth]);
        }

        $this->applySecondaryFilters($query, $req);

        $incomes = $query->orderBy('income_date', 'desc')->orderBy('id', 'desc')->get();

        $formatted = $incomes->map(fn($item) => (array) $this->formatIncomeRecord($item));

        $totalAmount = (float) $formatted->sum('received_amount');

        $isDaily = (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $period);
        $periodLabel = '';
        if ($isDaily) {
            $pDate = Carbon::parse($period);
            $periodLabel = app()->getLocale() === 'km'
                ? ('ថ្ងៃ' . __('other_income_report.days.' . strtolower($pDate->format('D'))) . ' ទី' . $pDate->format('d') . ' ' . __('other_income_report.months.' . $pDate->month) . ' ឆ្នាំ' . $pDate->format('Y'))
                : $pDate->format('l, d F Y');
        } else {
            $pMonth = Carbon::createFromFormat('Y-m', $period);
            $periodLabel = app()->getLocale() === 'km'
                ? (__('other_income_report.months.' . (int) $pMonth->month) . ' ឆ្នាំ' . $pMonth->year)
                : $pMonth->format('F Y');
        }

        return response()->json([
            'status'          => 'success',
            'period'          => $period,
            'period_label'    => $periodLabel,
            'count'           => $formatted->count(),
            'total_amount'    => $totalAmount,
            'received_total'  => $totalAmount,
            'incomes'         => $formatted,
        ]);
    }

    private function formatIncomeRecord($item): object
    {
        $statusKey = $item->payment_status ?: 'pending';
        $statusLabel = match ($statusKey) {
            'paid'      => __('other_income.payment_status.paid'),
            'partial'   => __('other_income.payment_status.partial'),
            'cancelled' => __('other_income.payment_status.cancelled'),
            default     => __('other_income.payment_status.pending'),
        };

        $badgeClass = match ($statusKey) {
            'paid'      => 'badge bg-success',
            'partial'   => 'badge bg-warning text-dark',
            'cancelled' => 'badge bg-danger',
            default     => 'badge bg-secondary',
        };

        return (object) [
            'id'                     => $item->id,
            'income_number'          => $item->income_number,
            'income_date'            => $item->income_date ? Carbon::parse($item->income_date)->format('Y-m-d') : '---',
            'income_date_formatted'  => $item->income_date ? Carbon::parse($item->income_date)->format('d M Y') : '---',
            'title'                  => $item->title,
            'category_id'            => $item->category_id,
            'category_name'          => $item->category?->name ?: __('other_income_report.table.na'),
            'supplier_id'            => $item->supplier_id,
            'supplier_name'          => $item->supplier?->name ?: '---',
            'shop_id'                => $item->shop_id,
            'shop_name'              => $item->shop?->name ?: __('other_income_report.filter.all_shops'),
            'amount'                 => (float) ($item->received_amount ?? 0),
            'received_amount'        => (float) ($item->received_amount ?? 0),
            'payment_status'         => $statusKey,
            'payment_status_label'   => $statusLabel,
            'badge_class'            => $badgeClass,
            'reference_no'           => $item->reference_no ?: '---',
            'calculation_quarter'    => $item->calculation_quarter ?: '---',
            'base_purchase_amount'   => (float) ($item->base_purchase_amount ?? 0),
            'bonus_rate_percent'     => (float) ($item->bonus_rate_percent ?? 0),
            'description'            => $item->description ?: '---',
            'created_by_name'        => $item->createdBy?->name ?: 'System',
        ];
    }

    private function getUngroupedPaginated($query, Request $req)
    {
        $paginated = (clone $query)
            ->with(['supplier', 'category', 'shop', 'createdBy', 'payments'])
            ->orderBy('income_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(50)
            ->appends($req->query());

        $paginated->getCollection()->transform(fn($item) => $this->formatIncomeRecord($item));

        return $paginated;
    }

    private function getUngroupedAll($query)
    {
        return (clone $query)
            ->with(['supplier', 'category', 'shop', 'createdBy', 'payments'])
            ->orderBy('income_date', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->map(fn($item) => $this->formatIncomeRecord($item));
    }

    private function buildFilteredIncomeQuery(Request $req, $fromDate, $toDate)
    {
        $query = OtherIncome::query()
            ->with(['supplier', 'category', 'shop', 'createdBy', 'payments'])
            ->where('status', 1);

        if ($fromDate && $toDate) {
            $query->whereBetween('income_date', [$fromDate, $toDate]);
        } elseif ($fromDate) {
            $query->whereDate('income_date', '>=', $fromDate);
        } elseif ($toDate) {
            $query->whereDate('income_date', '<=', $toDate);
        }

        $this->applySecondaryFilters($query, $req);

        return $query;
    }

    private function applySecondaryFilters($query, Request $req): void
    {
        if ($req->filled('supplier_id') && $req->supplier_id !== 'all') {
            $query->where('supplier_id', $req->supplier_id);
        }

        if ($req->filled('category_id') && $req->category_id !== 'all') {
            $query->where('category_id', $req->category_id);
        }

        if ($req->filled('shop_id') && $req->shop_id !== 'all') {
            $query->where('shop_id', $req->shop_id);
        }

        if ($req->filled('payment_status') && $req->payment_status !== 'all') {
            $query->where('payment_status', $req->payment_status);
        }

        if ($req->filled('quarter')) {
            $query->where('calculation_quarter', $req->quarter);
        }

        if ($req->filled('search')) {
            $search = trim($req->search);
            $query->where(function ($q) use ($search) {
                $q->where('income_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('reference_no', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('supplier', fn($sq) => $sq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('category', fn($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }
    }

    private function calculateSummaryMetrics($query): array
    {
        $incomes = $query->get();

        $totalAmount = 0.0;
        $supplierBonusTotal = 0.0;
        $supplierCounts = [];

        foreach ($incomes as $item) {
            $rec = (float) ($item->received_amount ?? 0);

            $totalAmount += $rec;

            if ($item->supplier_id) {
                $sId = $item->supplier_id;
                $supplierCounts[$sId] = ($supplierCounts[$sId] ?? 0.0) + $rec;
                $supplierBonusTotal += $rec;
            }
        }

        $topSupplierName = __('other_income_report.table.na');
        if (!empty($supplierCounts)) {
            arsort($supplierCounts);
            $topId = array_key_first($supplierCounts);
            $topSupplierName = Supplier::find($topId)?->name ?: "Supplier #{$topId}";
        }

        $count = $incomes->count();
        $avgTxn = $count > 0 ? ($totalAmount / $count) : 0.0;

        return [
            'total_transactions' => $count,
            'total_amount'       => round($totalAmount, 2),
            'total_received'     => round($totalAmount, 2),
            'supplier_bonus'     => round($supplierBonusTotal, 2),
            'top_supplier_name'  => $topSupplierName,
            'avg_transaction'    => round($avgTxn, 2),
        ];
    }

    private function aggregateDailyIncomes($query)
    {
        $incomes = $query->with(['supplier', 'category', 'shop', 'createdBy'])
            ->orderBy('income_date', 'desc')
            ->get();

        $grouped = $incomes->groupBy(function ($item) {
            return $item->income_date ? Carbon::parse($item->income_date)->format('Y-m-d') : 'unknown';
        });

        $rows = [];
        $index = 1;

        foreach ($grouped as $dateKey => $dayIncomes) {
            if ($dateKey === 'unknown') continue;

            $cDate = Carbon::parse($dateKey);
            $totalReceived = 0.0;
            $supplierAmounts = [];

            foreach ($dayIncomes as $item) {
                $rec = (float) ($item->received_amount ?? 0);
                $totalReceived += $rec;

                if ($item->supplier_id) {
                    $sName = $item->supplier?->name ?: ('Supplier #' . $item->supplier_id);
                    $supplierAmounts[$sName] = ($supplierAmounts[$sName] ?? 0) + $rec;
                }
            }

            $topSupplier = '---';
            if (!empty($supplierAmounts)) {
                arsort($supplierAmounts);
                $topSupplier = array_key_first($supplierAmounts);
            }

            $dayKey = strtolower($cDate->format('D'));
            $dayName = __('other_income_report.days.' . $dayKey);

            $rows[] = (object) [
                'index'              => $index++,
                'date'               => $dateKey,
                'date_formatted'     => $cDate->format('d M Y'),
                'day_key'            => $dayKey,
                'day_name'           => $dayName,
                'is_today'           => $cDate->isToday(),
                'is_weekend'         => $cDate->isWeekend(),
                'transactions_count' => $dayIncomes->count(),
                'total_amount'       => $totalReceived,
                'received_total'     => $totalReceived,
                'top_supplier'       => $topSupplier,
            ];
        }

        return collect($rows);
    }

    private function aggregateMonthlyIncomes($query)
    {
        $incomes = $query->with(['supplier', 'category', 'shop', 'createdBy'])
            ->orderBy('income_date', 'desc')
            ->get();

        $grouped = $incomes->groupBy(function ($item) {
            return $item->income_date ? Carbon::parse($item->income_date)->format('Y-m') : 'unknown';
        });

        $rows = [];
        $index = 1;

        foreach ($grouped as $monthKey => $monthIncomes) {
            if ($monthKey === 'unknown') continue;

            $cMonth = Carbon::createFromFormat('Y-m', $monthKey);
            $totalReceived = 0.0;
            $activeDays = [];
            $supplierAmounts = [];

            foreach ($monthIncomes as $item) {
                $rec = (float) ($item->received_amount ?? 0);
                $totalReceived += $rec;

                if ($item->income_date) {
                    $activeDays[Carbon::parse($item->income_date)->format('Y-m-d')] = true;
                }

                if ($item->supplier_id) {
                    $sName = $item->supplier?->name ?: ('Supplier #' . $item->supplier_id);
                    $supplierAmounts[$sName] = ($supplierAmounts[$sName] ?? 0) + $rec;
                }
            }

            $topSupplier = '---';
            if (!empty($supplierAmounts)) {
                arsort($supplierAmounts);
                $topSupplier = array_key_first($supplierAmounts);
            }

            $monthName = app()->getLocale() === 'km'
                ? (__('other_income_report.months.' . (int) $cMonth->month) . ' ' . $cMonth->year)
                : $cMonth->format('F Y');

            $rows[] = (object) [
                'index'              => $index++,
                'month_key'          => $monthKey,
                'month_name'         => $monthName,
                'short_month'        => $cMonth->format('M Y'),
                'year'               => $cMonth->format('Y'),
                'active_days'        => count($activeDays),
                'transactions_count' => $monthIncomes->count(),
                'total_amount'       => $totalReceived,
                'received_total'     => $totalReceived,
                'top_supplier'       => $topSupplier,
            ];
        }

        return collect($rows);
    }

    private function resolveDailyDateRange(Request $req): array
    {
        $now = Carbon::now();
        $preset = $req->get('preset');

        if ($preset === 'today') {
            return ['from' => $now->format('Y-m-d'), 'to' => $now->format('Y-m-d')];
        }
        if ($preset === 'yesterday') {
            $y = $now->copy()->subDay()->format('Y-m-d');
            return ['from' => $y, 'to' => $y];
        }
        if ($preset === '7days') {
            return ['from' => $now->copy()->subDays(6)->format('Y-m-d'), 'to' => $now->format('Y-m-d')];
        }
        if ($preset === '30days') {
            return ['from' => $now->copy()->subDays(29)->format('Y-m-d'), 'to' => $now->format('Y-m-d')];
        }
        if ($preset === 'this_month') {
            return ['from' => $now->copy()->startOfMonth()->format('Y-m-d'), 'to' => $now->format('Y-m-d')];
        }
        if ($preset === 'last_month') {
            $last = $now->copy()->subMonth();
            return ['from' => $last->copy()->startOfMonth()->format('Y-m-d'), 'to' => $last->copy()->endOfMonth()->format('Y-m-d')];
        }

        // Start with today when no date range has been selected.
        if (!$req->filled('from_date') && !$req->filled('to_date')) {
            $today = $now->format('Y-m-d');
            return [
                'from' => $today,
                'to'   => $today,
            ];
        }

        // Preserve the existing bounds for partially specified custom ranges.
        $from = $req->filled('from_date') ? $req->from_date : $now->copy()->startOfMonth()->format('Y-m-d');
        $to   = $req->filled('to_date')   ? $req->to_date   : $now->copy()->endOfMonth()->format('Y-m-d');

        return ['from' => $from, 'to' => $to];
    }

    private function resolveMonthlyDateRange(Request $req): array
    {
        $now = Carbon::now();
        $year = (int) ($req->filled('year') ? $req->year : $now->year);

        $fromMonth = (int) ($req->filled('from_month') ? $req->from_month : 1);
        $toMonth   = (int) ($req->filled('to_month')   ? $req->to_month   : 12);

        if (!$req->filled('from_month') && !$req->filled('to_month')) {
            $fromMonth = $toMonth = $now->month;
        }

        $fromMonth = max(1, min(12, $fromMonth));
        $toMonth   = max($fromMonth, min(12, $toMonth));

        return [
            'year'       => $year,
            'from_month' => $fromMonth,
            'to_month'   => $toMonth,
            'from'       => Carbon::create($year, $fromMonth, 1)->startOfMonth()->format('Y-m-d'),
            'to'         => Carbon::create($year, $toMonth, 1)->endOfMonth()->format('Y-m-d'),
        ];
    }

    private function extractFilters(Request $req, string $mode): array
    {
        return [
            'supplier_id'    => $req->get('supplier_id', ''),
            'category_id'    => $req->get('category_id', ''),
            'shop_id'        => $req->get('shop_id', ''),
            'payment_status' => $req->get('payment_status', 'all'),
            'quarter'        => $req->get('quarter', ''),
            'search'         => $req->get('search', ''),
            'preset'         => $req->get('preset', ''),
        ];
    }

    private function getFilterOptions(): array
    {
        return [
            'categories' => OtherIncomeCategory::where('status', 1)->orderBy('ordering')->get(['id', 'name']),
            'suppliers'  => Supplier::where('status', 1)->orderBy('name')->get(['id', 'name']),
            'shops'      => Shop::where('status', 1)->orderBy('name')->get(['id', 'name']),
        ];
    }

    private function getAvailableYears(): array
    {
        $currentYear = (int) Carbon::now()->year;
        return range($currentYear, $currentYear - 4);
    }
}
