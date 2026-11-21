<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Inventory\StockInRequest;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchasePayment;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\StockIn;
use App\Models\StockOnHand;
use App\Models\Supplier;
use App\Services\PurchaseService;
use App\Services\StockTransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class StockInController extends Controller
{
    protected $layout = 'admin::pages.inventoryManagement.stockIn.';
    private $routeName = 'stock-in';
    protected PurchaseService $purchaseService;
    protected StockTransactionService $stockTransaction;

    public function __construct(PurchaseService $purchaseService, StockTransactionService $stockTransaction)
    {
        $this->purchaseService = $purchaseService;
        $this->stockTransaction = $stockTransaction;
    }

    public function index(Request $req, $status = null)
    {
        $currentStatus = $status ?? ($req->status ?? 'all');
        if ($currentStatus === '1') {
            $currentStatus = 'all';
        }

        $data['status'] = $currentStatus;
        $data['routeName'] = $this->routeName;
        $data['shop'] = $req->shop_id ? Shop::find($req->shop_id) : null;
        $data['supplier'] = $req->supplier_id ? Supplier::find($req->supplier_id) : null;

        $exportRoute = 'admin-' . $this->routeName . '-export';
        $data['exportUrl'] = \Illuminate\Support\Facades\Route::has($exportRoute)
            ? route($exportRoute, $req->query())
            : url('admin/' . $this->routeName . '/export' . ($req->query() ? '?' . http_build_query($req->query()) : ''));

        // Tab counts
        $data['counts'] = [
            'all'              => Purchase::count(),
            'ordered'          => Purchase::where('order_status', 'ordered')->count(),
            'partial_received' => Purchase::where('order_status', 'partial_received')->count(),
            'received'         => Purchase::where('order_status', 'received')->count(),
            'draft'            => Purchase::where('order_status', 'draft')->count(),
            'trash'            => Purchase::onlyTrashed()->count(),
        ];

        $data['data'] = $this->purchaseService->queryPurchases($req, $currentStatus)->paginate(25)->appends($req->query());

        return view($this->layout . 'index', $data);
    }

    public function report(Request $req)
    {
        $data = $this->purchaseService->queryPurchases($req, $req->status ?? 'all')->get();
        return response()->json($data);
    }

    public function export(Request $req)
    {
        $items = $this->purchaseService->queryPurchases($req, $req->status ?? 'all')->get();
        $fileName = 'purchase_stock_in_export_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0",
        ];

        $columns = [
            __('stock_in.table.no') ?? 'Nº',
            __('stock_in.table.po_number') ?? 'PO Number',
            __('stock_in.table.supplier') ?? 'Supplier',
            __('stock_in.table.shop') ?? 'Shop',
            __('stock_in.table.date') ?? 'Order Date',
            __('stock_in.table.total_amount') ?? 'Total Amount',
            __('stock_in.table.paid_amount') ?? 'Paid Amount',
            __('stock_in.table.remaining_amount') ?? 'Remaining Due',
            __('stock_in.table.order_status') ?? 'Receiving Status',
            __('stock_in.table.payment_status') ?? 'Payment Status',
            __('stock_in.table.requested_by') ?? 'Created By',
            __('stock_in.table.remark') ?? 'Remark',
        ];

        $callback = function () use ($items, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM
            fputcsv($file, $columns);

            foreach ($items as $index => $item) {
                fputcsv($file, [
                    $index + 1,
                    $item->purchase_number,
                    $item->supplier_title ?? '---',
                    $item->shop_title ?? '---',
                    $item->created_date ?? '',
                    $item->total_amount ?? 0,
                    $item->paid_amount ?? 0,
                    $item->remaining_amount ?? 0,
                    $item->order_status_title ?? '---',
                    $item->payment_status_title ?? '---',
                    $item->created_by_title ?? '---',
                    $item->remark ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function onCreate(Request $req)
    {
        return view($this->layout . 'create', $this->formData());
    }

    public function onEdit(Request $req, $id = null)
    {
        $id = $id ?: $req->id;
        $purchase = Purchase::with(['items.product.category', 'items.product.uom', 'supplier', 'shop'])->find($id);

        if (!$purchase) {
            return redirect()->route('admin-' . $this->routeName . '-list', 'all');
        }

        if (!$purchase->can_edit) {
            Session::flash('warning', __('stock_in.message.cannot_edit_locked') ?? 'This purchase order cannot be modified because goods have been received or payments recorded.');
            return redirect()->route('admin-' . $this->routeName . '-view', $purchase->id);
        }

        return view($this->layout . 'create', $this->formData($purchase));
    }

    public function onView(Request $req, $id = null)
    {
        $id = $id ?: $req->id;
        $purchase = Purchase::withTrashed()->with([
            'items.product.category',
            'items.product.uom',
            'supplier',
            'shop',
            'createdBy',
            'receipts.items.product',
            'receipts.receivedBy',
            'payments.createdBy',
        ])->find($id);

        if (!$purchase) {
            return redirect()->route('admin-' . $this->routeName . '-list', 'all');
        }

        $data = [
            'id'        => $purchase->id,
            'purchase'  => $purchase,
            'routeName' => $this->routeName,
        ];

        return view($this->layout . 'detail', $data);
    }

    public function onSave(StockInRequest $req, $id = '')
    {
        try {
            if ($id) {
                $purchase = Purchase::findOrFail($id);
                if (!$purchase->can_edit) {
                    throw ValidationException::withMessages([
                        'order' => __('stock_in.message.cannot_edit_locked') ?? 'This purchase order cannot be modified because goods have been received or payments recorded.',
                    ]);
                }
                $this->purchaseService->updatePurchase($purchase, $req->all());
                $message = __('stock_in.message.update_success') ?? 'Purchase order updated successfully';
            } else {
                $purchase = $this->purchaseService->createPurchase($req->all(), $req);
                $message = __('stock_in.message.create_success') ?? 'Purchase stock in created successfully';
            }

            Session::flash('success', $message);

            if ($req->input('save_opt') === 'save_new') {
                return redirect()->route('admin-' . $this->routeName . '-create');
            }

            return redirect()->route('admin-' . $this->routeName . '-view', $purchase->id);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Session::flash('warning', $e->getMessage() ?: ($id ? __('stock_in.message.update_failed') : __('stock_in.message.create_failed')));
            return redirect()->back()->withInput();
        }
    }

    public function receiveGoods(Request $req, $id)
    {
        $req->validate([
            'received_date' => 'nullable|date',
            'reference_no'  => 'nullable|string|max:100',
            'remark'        => 'nullable|string|max:1000',
            'items'         => 'required|array|min:1',
            'items.*.purchase_item_id' => 'required|integer',
            'items.*.qty_to_receive'   => 'required|numeric|min:0',
        ]);

        try {
            $purchase = Purchase::findOrFail($id);
            $receipt = $this->purchaseService->receiveGoods(
                $purchase,
                $req->input('items', []),
                $req->only(['received_date', 'reference_no', 'remark'])
            );

            $message = __('stock_in.message.receive_success') ?? "Goods receipt {$receipt->receipt_number} recorded successfully";

            if ($req->wantsJson()) {
                return response()->json([
                    'status'  => 200,
                    'message' => $message,
                    'receipt' => $receipt,
                ]);
            }

            Session::flash('success', $message);
            return redirect()->route('admin-' . $this->routeName . '-view', $id);
        } catch (ValidationException $e) {
            if ($req->wantsJson()) {
                return response()->json(['message' => 'unsuccess', 'errors' => $e->errors()], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($req->wantsJson()) {
                return response()->json(['message' => $e->getMessage(), 'status' => 500], 500);
            }
            Session::flash('warning', $e->getMessage());
            return redirect()->back();
        }
    }

    public function recordPayment(Request $req, $id)
    {
        $req->validate([
            'amount'          => 'required|numeric|min:0.01',
            'payment_date'    => 'nullable|date',
            'payment_method'  => 'required|string',
            'reference_no'    => 'nullable|string|max:100',
            'note'            => 'nullable|string|max:1000',
            'payment_receipt' => 'nullable|file|mimes:jpeg,png,jpg,pdf,webp|max:10240',
        ]);

        try {
            $purchase = Purchase::findOrFail($id);
            $payment = $this->purchaseService->recordPayment(
                $purchase,
                $req->all(),
                $req->file('payment_receipt')
            );

            $message = __('stock_in.message.payment_success') ?? "Payment of \${$payment->amount} recorded successfully";

            if ($req->wantsJson()) {
                return response()->json([
                    'status'  => 200,
                    'message' => $message,
                    'payment' => $payment,
                ]);
            }

            Session::flash('success', $message);
            return redirect()->route('admin-' . $this->routeName . '-view', $id);
        } catch (ValidationException $e) {
            if ($req->wantsJson()) {
                return response()->json(['message' => 'unsuccess', 'errors' => $e->errors()], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($req->wantsJson()) {
                return response()->json(['message' => $e->getMessage(), 'status' => 500], 500);
            }
            Session::flash('warning', $e->getMessage());
            return redirect()->back();
        }
    }

    public function deletePayment(Request $req, $payment_id)
    {
        try {
            $payment = PurchasePayment::findOrFail($payment_id);
            $purchaseId = $payment->purchase_id;
            $this->purchaseService->deletePayment($payment);

            $message = __('stock_in.message.payment_delete_success') ?? 'Payment voided successfully';

            if ($req->wantsJson()) {
                return response()->json([
                    'status'  => 200,
                    'message' => $message,
                ]);
            }

            Session::flash('success', $message);
            return redirect()->route('admin-' . $routeName . '-view', $purchaseId);
        } catch (\Exception $e) {
            if ($req->wantsJson()) {
                return response()->json(['message' => $e->getMessage(), 'status' => 500], 500);
            }
            Session::flash('warning', $e->getMessage());
            return redirect()->back();
        }
    }

    public function fullReceive(Request $req, $id)
    {
        try {
            $purchase = Purchase::findOrFail($id);
            $receipt = $this->purchaseService->receiveAllRemaining($purchase, [
                'received_date' => $req->input('received_date', date('Y-m-d')),
                'reference_no'  => $req->input('reference_no'),
                'remark'        => $req->input('remark', 'Full goods receipt via action button'),
            ]);

            $message = __('stock_in.message.full_receive_success') ?? "Purchase order marked as fully received ({$receipt->receipt_number}).";

            if ($req->wantsJson()) {
                return response()->json([
                    'status'  => 200,
                    'message' => 'success',
                    'receipt' => $receipt,
                ]);
            }

            Session::flash('success', $message);
            return redirect()->route('admin-' . $this->routeName . '-list', 'received');
        } catch (ValidationException $e) {
            if ($req->wantsJson()) {
                return response()->json([
                    'status'  => 422,
                    'message' => 'unsuccess',
                    'error'   => collect($e->errors())->flatten()->first(),
                    'errors'  => $e->errors(),
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($req->wantsJson()) {
                return response()->json([
                    'status'  => 500,
                    'message' => 'unsuccess',
                    'error'   => $e->getMessage(),
                ], 500);
            }
            Session::flash('warning', $e->getMessage());
            return redirect()->back();
        }
    }

    public function updatePaymentStatus(Request $req, $id)
    {
        try {
            $purchase = Purchase::findOrFail($id);
            $paymentStatus = strtolower($req->input('payment_status', 'paid'));

            if ($paymentStatus === 'paid') {
                $payment = $this->purchaseService->markAsPaid($purchase, [
                    'payment_date'   => $req->input('payment_date', date('Y-m-d')),
                    'payment_method' => $req->input('payment_method', 'cash'),
                    'reference_no'   => $req->input('reference_no'),
                    'note'           => $req->input('note', 'Marked as Paid via action button'),
                ]);
                $message = __('stock_in.message.paid_success') ?? 'Purchase marked as Paid successfully.';
            } else {
                return response()->json([
                    'status'  => 422,
                    'message' => 'unsuccess',
                    'error'   => 'Unsupported payment status update.',
                ], 422);
            }

            if ($req->wantsJson()) {
                return response()->json([
                    'status'  => 200,
                    'message' => 'success',
                    'payment' => $payment,
                ]);
            }

            Session::flash('success', $message);
            return redirect()->route('admin-' . $this->routeName . '-list', 'all');
        } catch (ValidationException $e) {
            if ($req->wantsJson()) {
                return response()->json([
                    'status'  => 422,
                    'message' => 'unsuccess',
                    'error'   => collect($e->errors())->flatten()->first(),
                    'errors'  => $e->errors(),
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            if ($req->wantsJson()) {
                return response()->json([
                    'status'  => 500,
                    'message' => 'unsuccess',
                    'error'   => $e->getMessage(),
                ], 500);
            }
            Session::flash('warning', $e->getMessage());
            return redirect()->back();
        }
    }

    public function getPurchaseItems(Request $req, $id)
    {
        $purchase = Purchase::with(['items.product.uom', 'supplier', 'shop'])->findOrFail($id);
        return response()->json([
            'status'   => 200,
            'purchase' => $purchase,
            'items'    => $purchase->items->map(function ($it) {
                return [
                    'id'           => $it->id,
                    'product_name' => $it->product_name,
                    'uom_title'    => $it->uom_title,
                    'qty'          => (float) $it->qty,
                    'qty_received' => (float) $it->qty_received,
                    'remaining'    => (float) $it->remaining_qty,
                ];
            }),
        ]);
    }

    public function printInvoice(Request $req, $id)
    {
        $purchase = Purchase::with([
            'items.product.category',
            'items.product.uom',
            'supplier',
            'shop',
            'createdBy',
            'payments',
        ])->findOrFail($id);

        return view($this->layout . 'print', [
            'purchase'  => $purchase,
            'routeName' => $this->routeName,
        ]);
    }

    public function updateStatus($id, $status)
    {
        try {
            $purchase = Purchase::findOrFail($id);
            $purchase->update(['status' => $status]);

            Session::flash('success', (int) $status === 2 ? __('stock_in.message.disable_success') : __('stock_in.message.enable_success'));
            return response()->json(['message' => 'success', 'status' => 200]);
        } catch (\Exception $e) {
            Session::flash('warning', __('stock_in.message.status_failed'));
            return response()->json(['message' => 'unsuccess', 'status' => 404]);
        }
    }

    public function delete($id = '')
    {
        try {
            $purchase = Purchase::findOrFail($id);
            $this->purchaseService->deletePurchase($purchase);

            Session::flash('success', __('stock_in.message.delete_success') ?? 'Purchase deleted successfully');
            return response()->json(['message' => 'success', 'status' => 200]);
        } catch (\Exception $e) {
            Session::flash('warning', $e->getMessage() ?: __('stock_in.message.delete_failed'));
            return response()->json(['message' => 'unsuccess', 'status' => 404]);
        }
    }

    public function restore($id = '')
    {
        try {
            $purchase = Purchase::withTrashed()->findOrFail($id);
            $this->purchaseService->restorePurchase($purchase);

            Session::flash('success', __('stock_in.message.restore_success') ?? 'Purchase restored successfully');
            return response()->json(['message' => 'success', 'status' => 200]);
        } catch (\Exception $e) {
            Session::flash('warning', __('stock_in.message.restore_failed'));
            return response()->json(['message' => 'unsuccess', 'status' => 404]);
        }
    }

    public function destroy($id = '')
    {
        try {
            $purchase = Purchase::withTrashed()->findOrFail($id);
            $this->purchaseService->forceDeletePurchase($purchase);

            Session::flash('success', __('stock_in.message.destroy_success') ?? 'Purchase permanently deleted');
            return response()->json(['message' => 'success', 'status' => 200]);
        } catch (\Exception $e) {
            Session::flash('warning', __('stock_in.message.destroy_failed'));
            return response()->json(['message' => 'unsuccess', 'status' => 404]);
        }
    }

    private function formData(Purchase $purchase = null, $readonly = false)
    {
        $shopId = old('shop_id', $purchase?->shop_id);
        $supplierId = old('supplier_id', $purchase?->supplier_id);

        $initialItems = [];
        if ($purchase && $purchase->items) {
            foreach ($purchase->items as $item) {
                $initialItems[] = [
                    'id'               => $item->id,
                    'product_id'       => (string) $item->product_id,
                    'product_name'     => $item->product_name,
                    'product_category' => $item->category_title,
                    'product_uom'      => $item->uom_title,
                    'product_image'    => $item->product_image,
                    'current_stock'    => $this->currentStock($purchase->shop_id, $item->product_id),
                    'unit_cost'        => (float) $item->unit_cost,
                    'qty'              => (float) $item->qty,
                    'qty_received'     => (float) $item->qty_received,
                    'subtotal'         => (float) $item->subtotal,
                    'remark'           => $item->remark ?? '',
                    'dropdownOpen'     => false,
                    'searchQuery'      => '',
                    'uid'              => uniqid('row_'),
                ];
            }
        }

        return [
            'id'                => $purchase?->id ?? '',
            'purchase'          => $purchase,
            'routeName'         => $this->routeName,
            'readonly'          => $readonly,
            'selectedShop'      => $shopId ? Shop::find($shopId) : null,
            'selectedSupplier'  => $supplierId ? Supplier::find($supplierId) : null,
            'preloadedProducts' => $shopId ? $this->getShopProductsData($shopId) : [],
            'initialItems'      => $initialItems,
        ];
    }

    public function getShopProducts($shopId)
    {
        return response()->json([
            'status' => 200,
            'data'   => $this->getShopProductsData($shopId),
        ]);
    }

    public function getShopProductsData($shopId)
    {
        if (!$shopId) {
            return [];
        }

        // Return all active products, with shop product details & stock on hand at this shop
        $allProducts = Product::where('status', 1)
            ->with(['category', 'uom'])
            ->get();

        $stockOnHands = StockOnHand::where('shop_id', $shopId)
            ->pluck('current_stock', 'product_id');

        $assignedProductIds = ShopProduct::where('shop_id', $shopId)
            ->where('status', 1)
            ->pluck('product_id')
            ->flip();

        $list = [];

        foreach ($allProducts as $p) {
            $list[] = [
                'id'            => (string) $p->id,
                'name'          => $p->name,
                'category'      => $p->category?->name ?? '---',
                'uom'           => $p->uom?->name ?? '---',
                'image'         => $p->image_url ?? asset('images/logo/default.png'),
                'cost'          => (float) ($p->cost ?? 0.00),
                'current_stock' => (int) ($stockOnHands[$p->id] ?? 0),
                'is_assigned'   => isset($assignedProductIds[$p->id]),
            ];
        }

        usort($list, function ($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });

        return $list;
    }

    private function currentStock($shopId, $productId)
    {
        if (!$shopId || !$productId) {
            return 0;
        }

        return (int) (StockOnHand::where('shop_id', $shopId)
            ->where('product_id', $productId)
            ->value('current_stock') ?? 0);
    }
}
