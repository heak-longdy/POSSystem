<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\BuildsOrderListingQuery;
use App\Http\Requests\Admin\OrderBulkPrintRequest;
use App\Models\Shop;
use App\Services\OrderBulkPrintService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class OrderBulkPrintController extends Controller
{
    use BuildsOrderListingQuery;

    public function __construct(
        protected OrderBulkPrintService $bulkPrintService
    ) {}

    /**
     * Display the Order Bulk Printing listing page with filters and selectable orders.
     */
    public function index(Request $req): View
    {
        $status = $this->normalizeOrderStatusTab($req->status ?? 'all') ?? 'all';
        $paymentStatus = $this->orderPaymentStatusForTab($status)
            ?: $this->normalizePaymentStatus($req->payment_status);

        $query = $this->buildOrderListingQuery($req, $status);

        $orders = $query->with([
                'shop',
                'customer',
                'orderDetails' => function ($detail) {
                    $detail->withTrashed()->with(['service', 'product']);
                },
            ])
            ->orderBy('id', 'desc')
            ->paginate(50)
            ->appends($req->query());

        // Decorate orders for display
        foreach ($orders as $order) {
            $order->display_invoice_number = $order->invoice_number ?: sprintf('%05d', $order->id);
            $order->display_date = $order->order_date ? Carbon::parse($order->order_date)->format('Y-m-d') : '---';
            $order->display_shop_name = $order->shop?->name ?: '---';
            $order->display_customer_name = $order->customer?->name ?: ($order->customer?->phone ?: __('order.walk_in_customer'));
            $order->display_total_price = '$' . number_format((float) ($order->total_price ?? 0), 2);
            $order->display_payment_status = $order->payment_status ?: 'Pending';
        }

        $shops = Shop::where('status', 1)->orderBy('name')->get();

        return view('admin::pages.order-bulk-print.index', [
            'orders' => $orders,
            'shops' => $shops,
            'selectedShop' => $req->shop_id ? Shop::find($req->shop_id) : null,
            'status' => $status,
            'paymentStatus' => $paymentStatus,
            'fromDate' => $req->from_date ?: '',
            'toDate' => $req->to_date ?: '',
            'search' => $req->search ?: '',
            'maxOrders' => (int) config('order_printing.max_orders', 100),
            'maxDetailRows' => (int) config('order_printing.max_detail_rows', 2000),
        ]);
    }

    /**
     * Fetch all matching order IDs for the "Select All Matching" action.
     */
    public function selection(Request $req): JsonResponse
    {
        $maxOrders = (int) config('order_printing.max_orders', 100);
        $status = $this->normalizeOrderStatusTab($req->status ?? 'all') ?? 'all';

        $query = $this->buildOrderListingQuery($req, $status);
        $totalCount = $query->count();

        if ($totalCount > $maxOrders) {
            return response()->json([
                'success' => false,
                'over_limit' => true,
                'total_count' => $totalCount,
                'limit' => $maxOrders,
                'message' => __('order_bulk_print.validation.matching_limit_exceeded', [
                    'total' => $totalCount,
                    'max' => $maxOrders,
                ]),
            ], 422);
        }

        $ids = $query->orderBy('id', 'desc')->pluck('id')->all();

        return response()->json([
            'success' => true,
            'ids' => $ids,
            'count' => count($ids),
            'over_limit' => false,
        ]);
    }

    /**
     * Render the combined print preview page in a standalone view.
     */
    public function preview(OrderBulkPrintRequest $req): View
    {
        try {
            $data = $this->bulkPrintService->prepareForPreview(
                $req->order_ids,
                $req->document_type,
                $req->print_locale
            );

            return view('admin::pages.order-bulk-print.preview', $data);
        } catch (ValidationException $e) {
            return view('admin::pages.order-bulk-print.preview', [
                'error' => $e->validator->errors()->first(),
                'documentType' => $req->document_type ?? 'invoice',
                'locale' => $req->print_locale ?? 'en',
                'orders' => collect(),
                'items' => [],
                'totalCount' => 0,
                'totalDetailRows' => 0,
            ]);
        }
    }
}
