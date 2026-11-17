<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PurchasePaymentRequest;
use App\Http\Requests\Admin\PurchaseRequest;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\Shop;
use App\Models\Supplier;
use App\Services\PurchasePaymentService;
use App\Services\PurchaseService;
use App\Services\Tools;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class PurchaseController extends Controller
{
    protected $layout = 'admin::pages.purchase.';
    private $routeName = 'purchase';
    protected PurchaseService $purchaseService;
    protected PurchasePaymentService $paymentService;
    protected Tools $tools;

    public function __construct(
        PurchaseService $purchaseService,
        PurchasePaymentService $paymentService,
        Tools $tools
    ) {
        $this->purchaseService = $purchaseService;
        $this->paymentService = $paymentService;
        $this->tools = $tools;
    }

    /**
     * Query purchases matching filters.
     */
    public function queryPurchases(Request $req, $status = null)
    {
        $status = $status ?? $req->status ?? 'all';

        $query = Purchase::query()
            ->with([
                'supplier',
                'shop',
                'createdBy',
                'receivedBy',
                'items.product.category',
                'items.product.uom',
                'items.uom',
                'payments.createdBy',
            ]);

        // Status Tab
        if ($status === 'trash') {
            $query->onlyTrashed();
        } else {
            if ($status && $status !== 'all') {
                $query->where('status', $status);
            }
        }

        // Filters
        if ($req->filled('payment_status') && $req->payment_status !== 'all') {
            $query->where('payment_status', $req->payment_status);
        }

        if ($req->filled('supplier_id')) {
            $query->where('supplier_id', $req->supplier_id);
        }

        if ($req->filled('shop_id')) {
            $query->where('shop_id', $req->shop_id);
        }

        if ($req->filled('from_date')) {
            $query->whereDate('purchase_date', '>=', $req->from_date);
        }

        if ($req->filled('to_date')) {
            $query->whereDate('purchase_date', '<=', $req->to_date);
        }

        if ($req->filled('search')) {
            $search = trim($req->search);
            $query->where(function ($q) use ($search) {
                $q->where('purchase_number', 'LIKE', '%' . $search . '%')
                    ->orWhere('supplier_invoice_no', 'LIKE', '%' . $search . '%')
                    ->orWhere('remark', 'LIKE', '%' . $search . '%')
                    ->orWhereHas('supplier', function ($sq) use ($search) {
                        $sq->where('name', 'LIKE', '%' . $search . '%')
                            ->orWhere('phone', 'LIKE', '%' . $search . '%');
                    });
            });
        }

        return $query->orderBy('id', 'desc');
    }

    /**
     * Purchase Order Listing Page
     */
    public function index(Request $req)
    {
        $status = $req->status ?? 'all';
        $data['status'] = $status;
        $data['routeName'] = $this->routeName;

        $query = $this->queryPurchases($req, $status);

        // Summaries
        $summaryQuery = (clone $query);
        $data['totalPurchases'] = (clone $summaryQuery)->count();
        $data['totalVolume'] = (clone $summaryQuery)->sum('total_amount');
        $data['totalPaid'] = (clone $summaryQuery)->sum('paid_amount');
        $data['totalOutstanding'] = max(0, $data['totalVolume'] - $data['totalPaid']);

        $data['data'] = $query->paginate(50)->appends($req->query());
        $data['suppliers'] = Supplier::where('status', 1)->orderBy('name')->get();
        $data['shops'] = Shop::where('status', 1)->orderBy('name')->get();
        $data['filters'] = [
            'from_date'      => $req->get('from_date'),
            'to_date'        => $req->get('to_date'),
            'supplier_id'    => $req->get('supplier_id'),
            'shop_id'        => $req->get('shop_id'),
            'payment_status' => $req->get('payment_status'),
            'search'         => $req->get('search'),
        ];
        $data['exportUrl'] = route('admin-' . $this->routeName . '-export', request()->query());

        return view($this->layout . 'index', $data);
    }

    /**
     * JSON Stream for ExcelJS and dynamic UI
     */
    public function report(Request $req)
    {
        $status = $req->status ?? 'all';
        $purchases = $this->queryPurchases($req, $status)->get();

        return response()->json([
            'status' => 'success',
            'rows'   => $purchases,
        ]);
    }

    /**
     * Create Form
     */
    public function onCreate()
    {
        $data['id'] = '';
        $data['routeName'] = $this->routeName;
        $data['suppliers'] = Supplier::where('status', 1)->orderBy('name')->get();
        $data['shops'] = Shop::where('status', 1)->orderBy('name')->get();
        $data['products'] = Product::where('status', 1)->with(['category', 'uom'])->orderBy('name')->get();
        $data['nextPoNumber'] = $this->purchaseService->generateNextPurchaseNumber();
        $data['purchase'] = null;

        return view($this->layout . 'create', $data);
    }

    /**
     * Edit Form
     */
    public function onEdit(Request $req)
    {
        $purchase = Purchase::with(['items.product.category', 'items.product.uom', 'supplier', 'shop'])->findOrFail($req->id);

        if ($purchase->status === 'received') {
            Session::flash('warning', 'Received purchase orders cannot be modified.');
            return redirect()->route('admin-' . $this->routeName . '-view', $purchase->id);
        }

        $data['id'] = $purchase->id;
        $data['routeName'] = $this->routeName;
        $data['suppliers'] = Supplier::where('status', 1)->orderBy('name')->get();
        $data['shops'] = Shop::where('status', 1)->orderBy('name')->get();
        $data['products'] = Product::where('status', 1)->with(['category', 'uom'])->orderBy('name')->get();
        $data['nextPoNumber'] = $purchase->purchase_number;
        $data['purchase'] = $purchase;

        return view($this->layout . 'create', $data);
    }

    /**
     * View Purchase Order Details
     */
    public function onView(Request $req)
    {
        $purchase = Purchase::withTrashed()
            ->with([
                'items.product.category',
                'items.product.uom',
                'items.uom',
                'supplier',
                'shop',
                'createdBy',
                'receivedBy',
                'payments.createdBy',
            ])
            ->findOrFail($req->id);

        $data['purchase'] = $purchase;
        $data['routeName'] = $this->routeName;

        return view($this->layout . 'view', $data);
    }

    /**
     * Save / Update Purchase Order
     */
    public function Save(PurchaseRequest $req, $id = '')
    {
        DB::beginTransaction();
        try {
            $items = $req->input('items', []);

            if (empty($items) || !is_array($items)) {
                throw ValidationException::withMessages([
                    'items' => 'At least one product item is required.',
                ]);
            }

            if ($id) {
                $purchase = Purchase::findOrFail($id);
                $this->purchaseService->updatePurchase($purchase, $req->all(), $items);
                $message = __('purchase.message.update_success');
            } else {
                $purchase = $this->purchaseService->createPurchase($req->all(), $items);
                $message = __('purchase.message.create_success');
            }

            DB::commit();
            Session::flash('success', $message);

            if ($req->input('save_opt') === 'save_new') {
                return redirect()->route('admin-' . $this->routeName . '-create');
            }

            return redirect()->route('admin-' . $this->routeName . '-list', 'all');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('warning', $e->getMessage() ?: ($id ? __('purchase.message.update_failed') : __('purchase.message.create_failed')));
            return redirect()->back()->withInput();
        }
    }

    /**
     * Mark goods received and increase stock on hand
     */
    public function receiveGoods(Request $req, $id)
    {
        try {
            $purchase = Purchase::findOrFail($id);
            $quantities = $req->input('received_qty', []);

            $this->purchaseService->receivePurchase($purchase, $quantities);

            if ($req->expectsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => __('purchase.message.receive_success'),
                ]);
            }

            Session::flash('success', __('purchase.message.receive_success'));
            return redirect()->back();
        } catch (ValidationException $e) {
            if ($req->expectsJson()) {
                return response()->json(['status' => 'error', 'errors' => $e->errors()], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($req->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
            }
            Session::flash('warning', $e->getMessage() ?: __('purchase.message.receive_failed'));
            return redirect()->back();
        }
    }

    /**
     * Cancel purchase order and reverse stock safely
     */
    public function cancel(Request $req, $id)
    {
        try {
            $purchase = Purchase::findOrFail($id);
            $this->purchaseService->cancelPurchase($purchase);

            if ($req->expectsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => __('purchase.message.cancel_success'),
                ]);
            }

            Session::flash('success', __('purchase.message.cancel_success'));
            return redirect()->back();
        } catch (ValidationException $e) {
            if ($req->expectsJson()) {
                return response()->json(['status' => 'error', 'errors' => $e->errors()], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($req->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
            }
            Session::flash('warning', $e->getMessage() ?: __('purchase.message.cancel_failed'));
            return redirect()->back();
        }
    }

    /**
     * Record payment to supplier for purchase
     */
    public function storePayment(PurchasePaymentRequest $req, $id)
    {
        try {
            $purchase = Purchase::findOrFail($id);
            $payment = $this->paymentService->recordPayment($purchase, $req->all(), $req->file('receipt'));

            if ($req->expectsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => __('purchase.message.payment_success'),
                    'payment' => $payment->load('createdBy'),
                    'purchase'=> $purchase->fresh(),
                ]);
            }

            Session::flash('success', __('purchase.message.payment_success'));
            return redirect()->back();
        } catch (ValidationException $e) {
            if ($req->expectsJson()) {
                return response()->json(['status' => 'error', 'errors' => $e->errors()], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($req->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
            }
            Session::flash('warning', $e->getMessage() ?: __('purchase.message.payment_failed'));
            return redirect()->back();
        }
    }

    /**
     * Delete/void payment transaction
     */
    public function destroyPayment(Request $req, $id)
    {
        try {
            $payment = PurchasePayment::findOrFail($id);
            $this->paymentService->deletePayment($payment);

            if ($req->expectsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => __('purchase.message.payment_delete_success'),
                ]);
            }

            Session::flash('success', __('purchase.message.payment_delete_success'));
            return redirect()->back();
        } catch (\Exception $e) {
            if ($req->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
            }
            Session::flash('warning', $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Soft Delete Purchase
     */
    public function delete($id = '')
    {
        $purchase = Purchase::findOrFail($id);
        if ($purchase->status === 'received') {
            Session::flash('warning', 'Cannot delete a received purchase order. Cancel it first to reverse stock.');
            return redirect()->back();
        }

        $purchase->delete();
        Session::flash('success', __('purchase.message.delete_success'));
        return redirect()->back();
    }

    /**
     * Restore Soft-deleted Purchase
     */
    public function restore($id = '')
    {
        $purchase = Purchase::onlyTrashed()->findOrFail($id);
        $purchase->restore();
        Session::flash('success', __('purchase.message.restore_success'));
        return redirect()->back();
    }

    /**
     * Permanent Delete
     */
    public function destroy($id = '')
    {
        $purchase = Purchase::onlyTrashed()->findOrFail($id);
        $purchase->forceDelete();
        Session::flash('success', __('purchase.message.destroy_success'));
        return redirect()->back();
    }

    /**
     * Print Purchase Order / Goods Receipt Voucher
     */
    public function print(Request $req, $id)
    {
        $purchase = Purchase::with([
            'items.product.category',
            'items.product.uom',
            'items.uom',
            'supplier',
            'shop',
            'createdBy',
            'receivedBy',
            'payments.createdBy',
        ])->findOrFail($id);

        return view($this->layout . 'print', [
            'purchase'  => $purchase,
            'routeName' => $this->routeName,
        ]);
    }

    /**
     * Export to CSV fallback
     */
    public function export(Request $req)
    {
        $items = $this->queryPurchases($req)->get();
        $fileName = 'purchase_orders_export_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=$fileName",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $columns = [
            __('purchase.table.no'),
            __('purchase.table.purchase_number'),
            __('purchase.table.supplier_invoice_no'),
            __('purchase.table.supplier'),
            __('purchase.table.shop'),
            __('purchase.table.date'),
            __('purchase.table.due_date'),
            __('purchase.table.total_amount'),
            __('purchase.table.paid_amount'),
            __('purchase.table.remaining_amount'),
            __('purchase.table.status'),
            __('purchase.table.payment_status'),
        ];

        $callback = function () use ($items, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM
            fputcsv($file, $columns);

            foreach ($items as $index => $item) {
                fputcsv($file, [
                    $index + 1,
                    $item->purchase_number,
                    $item->supplier_invoice_no ?? '---',
                    $item->supplier?->name ?? '---',
                    $item->shop?->name ?? '---',
                    $item->purchase_date ? Carbon::parse($item->purchase_date)->format('Y-m-d') : '',
                    $item->payment_due_date ? Carbon::parse($item->payment_due_date)->format('Y-m-d') : '',
                    number_format($item->total_amount ?? 0, 2),
                    number_format($item->paid_amount ?? 0, 2),
                    number_format($item->remaining_amount ?? 0, 2),
                    ucfirst($item->status ?? ''),
                    ucfirst($item->payment_status ?? ''),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
