<?php

namespace App\Http\Controllers\Admin\Report;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Shop;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseReportController extends Controller
{
    protected $layout = 'admin::pages.report.purchase.';
    private $routeName = 'report-purchase';

    public function __construct()
    {
        $this->middleware('permission:report-purchase-view', [
            'only' => ['index', 'daily', 'monthly', 'report', 'details'],
        ]);
    }

    private function resolveGrouping(Request $req): string
    {
        $grouping = $req->get('grouping', 'ungrouped');
        return in_array($grouping, ['grouped', 'ungrouped'], true) ? $grouping : 'ungrouped';
    }

    public function index(Request $req)
    {
        return redirect()->route('admin-' . $this->routeName . '-daily', $req->query());
    }

    /**
     * Daily Purchase Report View
     */
    public function daily(Request $req)
    {
        $grouping = $this->resolveGrouping($req);
        $dates = $this->resolveDailyDateRange($req);
        $appliedFilters = $this->extractFilters($req, 'daily');

        $baseQuery = $this->buildFilteredPurchaseQuery($req, $dates['from'], $dates['to']);
        $summary = $this->calculateSummaryMetrics(clone $baseQuery);

        $rows = null;
        $ungroupedRows = null;

        if ($grouping === 'grouped') {
            $rows = $this->aggregateDailyPurchases(clone $baseQuery);
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
            'suppliers'     => $filterOptions['suppliers'],
            'shops'         => $filterOptions['shops'],
        ]);
    }

    /**
     * Monthly Purchase Report View
     */
    public function monthly(Request $req)
    {
        $grouping = $this->resolveGrouping($req);
        $monthRange = $this->resolveMonthlyDateRange($req);
        $appliedFilters = $this->extractFilters($req, 'monthly');

        $baseQuery = $this->buildFilteredPurchaseQuery($req, $monthRange['from'], $monthRange['to']);
        $summary = $this->calculateSummaryMetrics(clone $baseQuery);

        $rows = null;
        $ungroupedRows = null;

        if ($grouping === 'grouped') {
            $rows = $this->aggregateMonthlyPurchases(clone $baseQuery);
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
            'suppliers'      => $filterOptions['suppliers'],
            'shops'          => $filterOptions['shops'],
        ]);
    }

    /**
     * JSON Stream for ExcelJS client export
     */
    public function report(Request $req)
    {
        $viewMode = $req->get('viewMode', 'daily');
        if ($viewMode === 'monthly') {
            $range = $this->resolveMonthlyDateRange($req);
        } else {
            $range = $this->resolveDailyDateRange($req);
        }

        $baseQuery = $this->buildFilteredPurchaseQuery($req, $range['from'], $range['to']);
        $grouping = $this->resolveGrouping($req);

        if ($grouping === 'grouped') {
            $rows = $viewMode === 'monthly'
                ? $this->aggregateMonthlyPurchases(clone $baseQuery)
                : $this->aggregateDailyPurchases(clone $baseQuery);
        } else {
            $rows = (clone $baseQuery)
                ->with(['supplier', 'shop', 'createdBy', 'receivedBy', 'items.product'])
                ->orderBy('purchase_date', 'desc')
                ->orderBy('id', 'desc')
                ->get();
        }

        return response()->json([
            'status'   => 'success',
            'rows'     => $rows,
            'summary'  => $this->calculateSummaryMetrics(clone $baseQuery),
            'viewMode' => $viewMode,
            'grouping' => $grouping,
        ]);
    }

    private function buildFilteredPurchaseQuery(Request $req, string $fromDate, string $toDate)
    {
        $query = Purchase::query()
            ->whereDate('purchase_date', '>=', $fromDate)
            ->whereDate('purchase_date', '<=', $toDate);

        if ($req->filled('supplier_id')) {
            $query->where('supplier_id', $req->supplier_id);
        }

        if ($req->filled('shop_id')) {
            $query->where('shop_id', $req->shop_id);
        }

        if ($req->filled('status') && $req->status !== 'all') {
            $query->where('status', $req->status);
        }

        if ($req->filled('payment_status') && $req->payment_status !== 'all') {
            $query->where('payment_status', $req->payment_status);
        }

        if ($req->filled('search')) {
            $search = trim($req->search);
            $query->where(function ($q) use ($search) {
                $q->where('purchase_number', 'LIKE', '%' . $search . '%')
                    ->orWhere('supplier_invoice_no', 'LIKE', '%' . $search . '%')
                    ->orWhereHas('supplier', function ($sq) use ($search) {
                        $sq->where('name', 'LIKE', '%' . $search . '%');
                    });
            });
        }

        return $query;
    }

    private function calculateSummaryMetrics($query): array
    {
        $totalPurchases = (clone $query)->count();
        $totalVolume = (clone $query)->sum('total_amount');
        $totalPaid = (clone $query)->sum('paid_amount');
        $totalOutstanding = max(0.0, $totalVolume - $totalPaid);

        return [
            'total_purchases'   => $totalPurchases,
            'total_volume'      => (float) $totalVolume,
            'total_paid'        => (float) $totalPaid,
            'total_outstanding' => (float) $totalOutstanding,
        ];
    }

    private function aggregateDailyPurchases($query)
    {
        return (clone $query)
            ->selectRaw('DATE(purchase_date) as date, COUNT(*) as count, SUM(total_amount) as total_amount, SUM(paid_amount) as paid_amount, SUM(total_amount - paid_amount) as remaining_amount')
            ->groupBy(DB::raw('DATE(purchase_date)'))
            ->orderBy('date', 'desc')
            ->get();
    }

    private function aggregateMonthlyPurchases($query)
    {
        return (clone $query)
            ->selectRaw("DATE_FORMAT(purchase_date, '%Y-%m') as month, COUNT(*) as count, SUM(total_amount) as total_amount, SUM(paid_amount) as paid_amount, SUM(total_amount - paid_amount) as remaining_amount")
            ->groupBy(DB::raw("DATE_FORMAT(purchase_date, '%Y-%m')"))
            ->orderBy('month', 'desc')
            ->get();
    }

    private function getUngroupedPaginated($query, Request $req)
    {
        return (clone $query)
            ->with(['supplier', 'shop', 'createdBy', 'items.product'])
            ->orderBy('purchase_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(50)
            ->appends($req->query());
    }

    private function resolveDailyDateRange(Request $req): array
    {
        $from = $req->get('from_date', now()->startOfMonth()->toDateString());
        $to = $req->get('to_date', now()->toDateString());
        return ['from' => $from, 'to' => $to];
    }

    private function resolveMonthlyDateRange(Request $req): array
    {
        $year = (int) $req->get('year', date('Y'));
        $fromMonth = (int) $req->get('from_month', 1);
        $toMonth = (int) $req->get('to_month', (int) date('m'));

        $from = Carbon::createFromDate($year, $fromMonth, 1)->startOfMonth()->toDateString();
        $to = Carbon::createFromDate($year, $toMonth, 1)->endOfMonth()->toDateString();

        return [
            'year'       => $year,
            'from_month' => $fromMonth,
            'to_month'   => $toMonth,
            'from'       => $from,
            'to'         => $to,
        ];
    }

    private function extractFilters(Request $req, string $mode): array
    {
        return [
            'supplier_id'    => $req->get('supplier_id'),
            'shop_id'        => $req->get('shop_id'),
            'status'         => $req->get('status'),
            'payment_status' => $req->get('payment_status'),
            'search'         => $req->get('search'),
        ];
    }

    private function getFilterOptions(): array
    {
        return [
            'suppliers' => Supplier::where('status', 1)->orderBy('name')->get(),
            'shops'     => Shop::where('status', 1)->orderBy('name')->get(),
        ];
    }
}
