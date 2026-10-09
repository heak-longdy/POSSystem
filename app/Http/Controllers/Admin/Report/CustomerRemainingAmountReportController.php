<?php

namespace App\Http\Controllers\Admin\Report;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Shop;
use App\Services\CustomerRemainingAmountReportService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CustomerRemainingAmountReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:report-sales-view|order-view');
    }

    public function index(Request $request, CustomerRemainingAmountReportService $report)
    {
        $filters = $request->validate([
            'customer_id' => 'nullable|integer|exists:customers,id',
            'search' => 'nullable|string|max:255',
            'shop_id' => 'nullable|integer|exists:shops,id',
            'page' => 'nullable|integer|min:1',
        ]);
        $query = $report->customers($filters);
        $summary = DB::query()->fromSub(clone $query, 'customer_balances')
            ->selectRaw('COUNT(*) AS customer_count, COALESCE(SUM(total_amount), 0) AS total_amount, '
                . 'COALESCE(SUM(paid_amount), 0) AS paid_amount, COALESCE(SUM(remaining_amount), 0) AS remaining_amount')
            ->first();
        $customers = $query->orderByDesc('balances.remaining_amount')->orderBy('customers.id')
            ->paginate(25)->withQueryString();

        return view('admin::pages.report.customerRemaining.index', [
            'customers' => $customers,
            'summary' => $summary,
            'filters' => $filters,
            'shops' => Shop::withTrashed()->orderBy('name')->get(['id', 'name']),
            'customerList' => Customer::withTrashed()->orderBy('name')->get(['id', 'name', 'phone']),
        ]);
    }

    public function show(Request $request, $customer, CustomerRemainingAmountReportService $report)
    {
        $filters = $request->validate([
            'shop_id' => 'nullable|integer|exists:shops,id',
            'from_date' => 'nullable|date_format:Y-m-d',
            'to_date' => 'nullable|date_format:Y-m-d' . ($request->filled('from_date') ? '|after_or_equal:from_date' : ''),
            'page' => 'nullable|integer|min:1',
        ]);
        $customer = Customer::withTrashed()->findOrFail($customer);
        $orders = $report->outstandingOrders($filters['shop_id'] ?? null)
            ->where('customer_id', $customer->id)
            ->with([
                'shop' => fn ($query) => $query->withTrashed(),
                'payments.createdBy' => fn ($query) => $query->withTrashed(),
            ])->get();
        $history = $report->history($orders, $filters['from_date'] ?? null, $filters['to_date'] ?? null);
        // Settling invoices can remove the last page while this tab is open.
        $page = min($filters['page'] ?? 1, max(1, (int) ceil($history['invoices']->count() / 10)));
        $invoices = new LengthAwarePaginator(
            $history['invoices']->forPage($page, 10)->values(), $history['invoices']->count(), 10, $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin::pages.report.customerRemaining.detail', [
            'customer' => $customer,
            'history' => $history,
            'invoices' => $invoices,
            'filters' => $filters,
            'shops' => Shop::withTrashed()->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
