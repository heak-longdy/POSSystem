<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchasePayment;
use App\Models\PurchaseReceipt;
use App\Models\PurchaseReceiptItem;
use App\Models\StockHistory;
use App\Models\StockIn;
use App\Models\UploadFile;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    protected StockTransactionService $stockTransaction;

    public function __construct(StockTransactionService $stockTransaction)
    {
        $this->stockTransaction = $stockTransaction;
    }

    /**
     * Build filtered query for Purchases
     */
    public function queryPurchases(Request $req, $status = 'all'): Builder
    {
        if ($status === 'trash') {
            $query = Purchase::onlyTrashed();
        } else {
            $query = Purchase::query();

            if ($status && $status !== 'all') {
                $query->where('order_status', $status);
            }
        }

        $query->with([
            'supplier',
            'shop',
            'createdBy',
            'items.product.category',
            'items.product.uom',
            'receipts.items',
            'payments',
        ]);

        if ($req->filled('shop_id')) {
            $query->where('shop_id', (int) $req->shop_id);
        }

        if ($req->filled('supplier_id')) {
            $query->where('supplier_id', (int) $req->supplier_id);
        }

        if ($req->filled('payment_status')) {
            $query->where('payment_status', $req->payment_status);
        }

        if ($req->filled('order_status') && (!$status || $status === 'all')) {
            $query->where('order_status', $req->order_status);
        }

        if ($req->filled('from_date')) {
            $query->whereDate('order_date', '>=', $req->from_date);
        }

        if ($req->filled('to_date')) {
            $query->whereDate('order_date', '<=', $req->to_date);
        }

        if ($req->filled('search')) {
            $search = trim($req->search);
            $query->where(function ($q) use ($search) {
                $q->where('purchase_number', 'like', "%{$search}%")
                    ->orWhere('reference_no', 'like', "%{$search}%")
                    ->orWhere('remark', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('shop', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('items.product', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        return $query->orderBy('order_date', 'desc')->orderBy('id', 'desc');
    }

    /**
     * Create a new Purchase Order
     */
    public function createPurchase(array $data, Request $request = null): Purchase
    {
        return DB::transaction(function () use ($data, $request) {
            $orderDate = !empty($data['order_date']) ? Carbon::parse($data['order_date'])->format('Y-m-d') : date('Y-m-d');
            $expectedDate = !empty($data['expected_delivery_date']) ? Carbon::parse($data['expected_delivery_date'])->format('Y-m-d') : null;

            $purchaseNumber = !empty($data['purchase_number']) ? $data['purchase_number'] : null;

            $purchase = new Purchase();
            $purchase->purchase_number       = $purchaseNumber;
            $purchase->supplier_id           = !empty($data['supplier_id']) ? (int) $data['supplier_id'] : null;
            $purchase->shop_id               = (int) $data['shop_id'];
            $purchase->order_date            = $orderDate;
            $purchase->expected_delivery_date = $expectedDate;
            $purchase->order_status          = !empty($data['order_status']) ? $data['order_status'] : 'ordered';
            $purchase->discount_amount       = !empty($data['discount_amount']) ? (float) $data['discount_amount'] : 0.00;
            $purchase->tax_amount            = !empty($data['tax_amount']) ? (float) $data['tax_amount'] : 0.00;
            $purchase->shipping_cost         = !empty($data['shipping_cost']) ? (float) $data['shipping_cost'] : 0.00;
            $purchase->reference_no          = $data['reference_no'] ?? null;
            $purchase->remark                = $data['remark'] ?? null;
            $purchase->payment_method        = $data['initial_payment_method'] ?? 'cash';
            $purchase->created_by            = Auth::id() ?? 1;
            $purchase->status                = 1;
            $purchase->save();

            $subtotal = 0.00;
            $items = $data['items'] ?? [];
            $createdItems = [];

            foreach ($items as $itemData) {
                if (empty($itemData['product_id']) || empty($itemData['qty']) || (float) $itemData['qty'] <= 0) {
                    continue;
                }

                $qty = (float) $itemData['qty'];
                $unitCost = isset($itemData['unit_cost']) ? (float) $itemData['unit_cost'] : 0.00;
                $lineSubtotal = round($qty * $unitCost, 2);
                $subtotal += $lineSubtotal;

                $item = new PurchaseItem();
                $item->purchase_id   = $purchase->id;
                $item->product_id    = (int) $itemData['product_id'];
                $item->unit_cost     = $unitCost;
                $item->qty           = $qty;
                $item->qty_received  = 0.00;
                $item->subtotal      = $lineSubtotal;
                $item->remark        = $itemData['remark'] ?? null;
                $item->save();

                $createdItems[] = $item;
            }

            if (empty($createdItems)) {
                throw ValidationException::withMessages([
                    'items' => __('stock_in.validation.items_required') ?? 'At least one valid item is required',
                ]);
            }

            $purchase->subtotal = round($subtotal, 2);
            $total = $purchase->subtotal - $purchase->discount_amount + $purchase->tax_amount + $purchase->shipping_cost;
            $purchase->total_amount = max(0.00, round($total, 2));
            $purchase->save();

            // Direct Purchase & Stock In (Immediate Goods Inflow)
            $isDirectStockIn = !empty($data['immediate_stock_in']) || (!empty($data['fulfillment_type']) && $data['fulfillment_type'] === 'direct');

            if ($isDirectStockIn) {
                $receipt = new PurchaseReceipt();
                $receipt->purchase_id    = $purchase->id;
                $receipt->receipt_number = PurchaseReceipt::generateReceiptNumber($orderDate);
                $receipt->received_date  = $orderDate;
                $receipt->received_by    = Auth::id() ?? 1;
                $receipt->reference_no   = $purchase->reference_no;
                $receipt->remark         = 'Direct purchase stock-in upon order creation';
                $receipt->save();

                foreach ($createdItems as $createdItem) {
                    $itemQty = (float) $createdItem->qty;

                    // Increase stock in warehouse
                    $currentStock = $this->stockTransaction->increaseStock(
                        $purchase->shop_id,
                        $createdItem->product_id,
                        $itemQty,
                        $createdItem->remark ?? $purchase->remark
                    );

                    // Ensure shop product exists
                    $this->stockTransaction->syncShopProduct($purchase->shop_id, $createdItem->product_id);

                    // Create legacy StockIn record for system-wide compatibility
                    $stockIn = StockIn::create([
                        'purchase_id'         => $purchase->id,
                        'purchase_receipt_id' => $receipt->id,
                        'product_id'          => $createdItem->product_id,
                        'shop_id'             => $purchase->shop_id,
                        'supplier_id'         => $purchase->supplier_id,
                        'supplier_type'       => 'supplier',
                        'qty'                 => $itemQty,
                        'remark'              => $createdItem->remark ?? $purchase->remark,
                        'status'              => 1,
                        'request_by'          => Auth::id() ?? 1,
                        'request_by_type'     => 'admin',
                    ]);

                    // Find or create StockHistory
                    $stockHistory = StockHistory::updateOrCreate([
                        'stock_id' => $stockIn->id,
                        'status'   => 'stock_in',
                    ], [
                        'product_id'      => $createdItem->product_id,
                        'current_stock'   => $currentStock,
                        'stock_in'        => $itemQty,
                        'stock_out'       => 0,
                        'shop_id'         => $purchase->shop_id,
                        'to_id'           => $purchase->supplier_id,
                        'qty'             => $itemQty,
                        'remark'          => $createdItem->remark ?? $purchase->remark,
                        'type'            => 'shop',
                        'request_by'      => Auth::id() ?? 1,
                        'request_by_type' => 'admin',
                    ]);

                    // Create receipt item
                    $receiptItem = new PurchaseReceiptItem();
                    $receiptItem->purchase_receipt_id = $receipt->id;
                    $receiptItem->purchase_item_id    = $createdItem->id;
                    $receiptItem->product_id          = $createdItem->product_id;
                    $receiptItem->qty_received        = $itemQty;
                    $receiptItem->stock_history_id    = $stockHistory->id;
                    $receiptItem->save();

                    // Update item received qty
                    $createdItem->qty_received = $itemQty;
                    $createdItem->save();
                }

                $purchase->order_status = 'received';
            }

            // Upfront initial payment
            $initialPayment = !empty($data['initial_payment_amount']) ? (float) $data['initial_payment_amount'] : 0.00;
            if ($initialPayment > 0.00) {
                $payment = new PurchasePayment();
                $payment->purchase_id     = $purchase->id;
                $payment->payment_number  = PurchasePayment::generatePaymentNumber($orderDate);
                $payment->amount          = $initialPayment;
                $payment->payment_date    = !empty($data['payment_date']) ? Carbon::parse($data['payment_date'])->format('Y-m-d') : $orderDate;
                $payment->payment_method  = $data['initial_payment_method'] ?? 'cash';
                $payment->reference_no    = $data['payment_reference_no'] ?? null;
                $payment->note            = $data['payment_note'] ?? 'Initial purchase payment';
                $payment->created_by      = Auth::id() ?? 1;

                if ($request && $request->hasFile('payment_receipt')) {
                    $receiptFile = $request->file('payment_receipt');
                    $destination = '/purchase/receipts';
                    $uploadedReceipt = UploadFile::saveFile2($destination, $receiptFile);
                    $payment->receipt_path = 'uploads' . $destination . '/' . $uploadedReceipt['unique_name'];
                    $payment->receipt_name = $uploadedReceipt['original_name'];
                }

                $payment->save();
            }

            $purchase->recalculateTotals();

            return $purchase;
        });
    }

    /**
     * Update an existing Purchase Order
     */
    public function updatePurchase(Purchase $purchase, array $data): Purchase
    {
        return DB::transaction(function () use ($purchase, $data) {
            if (!$purchase->can_edit) {
                throw ValidationException::withMessages([
                    'order' => __('stock_in.message.cannot_edit_locked') ?? 'This purchase order cannot be modified because goods have been received or payments recorded.',
                ]);
            }

            $orderDate = !empty($data['order_date']) ? Carbon::parse($data['order_date'])->format('Y-m-d') : $purchase->order_date;
            $expectedDate = !empty($data['expected_delivery_date']) ? Carbon::parse($data['expected_delivery_date'])->format('Y-m-d') : null;

            $purchase->supplier_id           = !empty($data['supplier_id']) ? (int) $data['supplier_id'] : $purchase->supplier_id;
            $purchase->shop_id               = (int) $data['shop_id'];
            $purchase->order_date            = $orderDate;
            $purchase->expected_delivery_date = $expectedDate;
            $purchase->discount_amount       = isset($data['discount_amount']) ? (float) $data['discount_amount'] : (float) $purchase->discount_amount;
            $purchase->tax_amount            = isset($data['tax_amount']) ? (float) $data['tax_amount'] : (float) $purchase->tax_amount;
            $purchase->shipping_cost         = isset($data['shipping_cost']) ? (float) $data['shipping_cost'] : (float) $purchase->shipping_cost;
            $purchase->reference_no          = $data['reference_no'] ?? $purchase->reference_no;
            $purchase->remark                = $data['remark'] ?? $purchase->remark;

            if (!empty($data['order_status']) && in_array($data['order_status'], ['draft', 'cancelled'])) {
                $purchase->order_status = $data['order_status'];
            }

            $itemsData = $data['items'] ?? [];
            $existingItems = $purchase->items->keyBy('id');
            $processedIds = [];
            $subtotal = 0.00;

            foreach ($itemsData as $row) {
                if (empty($row['product_id']) || empty($row['qty']) || (float) $row['qty'] <= 0) {
                    continue;
                }

                $qty = (float) $row['qty'];
                $unitCost = isset($row['unit_cost']) ? (float) $row['unit_cost'] : 0.00;
                $rowId = !empty($row['id']) ? (int) $row['id'] : null;

                if ($rowId && $existingItems->has($rowId)) {
                    $item = $existingItems->get($rowId);

                    // Ensure qty is not reduced below already received qty
                    if ($qty < (float) $item->qty_received) {
                        throw ValidationException::withMessages([
                            'items' => "Ordered quantity for {$item->product_name} cannot be less than already received quantity ({$item->qty_received}).",
                        ]);
                    }

                    $item->product_id = (int) $row['product_id'];
                    $item->unit_cost  = $unitCost;
                    $item->qty        = $qty;
                    $item->subtotal   = round($qty * $unitCost, 2);
                    $item->remark     = $row['remark'] ?? null;
                    $item->save();

                    $processedIds[] = $item->id;
                    $subtotal += $item->subtotal;
                } else {
                    $item = new PurchaseItem();
                    $item->purchase_id  = $purchase->id;
                    $item->product_id   = (int) $row['product_id'];
                    $item->unit_cost    = $unitCost;
                    $item->qty          = $qty;
                    $item->qty_received = 0.00;
                    $item->subtotal     = round($qty * $unitCost, 2);
                    $item->remark       = $row['remark'] ?? null;
                    $item->save();

                    $processedIds[] = $item->id;
                    $subtotal += $item->subtotal;
                }
            }

            // Remove unmentioned items only if not yet received
            foreach ($existingItems as $existingItem) {
                if (!in_array($existingItem->id, $processedIds)) {
                    if ((float) $existingItem->qty_received > 0) {
                        throw ValidationException::withMessages([
                            'items' => "Cannot remove {$existingItem->product_name} because {$existingItem->qty_received} units have already been received.",
                        ]);
                    }
                    $existingItem->delete();
                }
            }

            $purchase->subtotal = round($subtotal, 2);
            $total = $purchase->subtotal - $purchase->discount_amount + $purchase->tax_amount + $purchase->shipping_cost;
            $purchase->total_amount = max(0.00, round($total, 2));
            $purchase->save();

            $purchase->recalculateTotals();

            return $purchase;
        });
    }

    /**
     * Process Goods Receiving against a Purchase Order
     */
    public function receiveGoods(Purchase $purchase, array $itemsReceiving, array $meta = []): PurchaseReceipt
    {
        return DB::transaction(function () use ($purchase, $itemsReceiving, $meta) {
            $receivedDate = !empty($meta['received_date']) ? Carbon::parse($meta['received_date'])->format('Y-m-d') : date('Y-m-d');
            $receiptNumber = PurchaseReceipt::generateReceiptNumber($receivedDate);

            $receipt = new PurchaseReceipt();
            $receipt->purchase_id    = $purchase->id;
            $receipt->receipt_number = $receiptNumber;
            $receipt->received_date  = $receivedDate;
            $receipt->received_by    = Auth::id() ?? 1;
            $receipt->reference_no   = $meta['reference_no'] ?? null;
            $receipt->remark         = $meta['remark'] ?? null;
            $receipt->save();

            $itemsMap = $purchase->items->keyBy('id');
            $hasReceivedAny = false;

            foreach ($itemsReceiving as $row) {
                $itemId = (int) ($row['purchase_item_id'] ?? 0);
                $qtyToReceive = (float) ($row['qty_to_receive'] ?? 0);

                if (!$itemId || $qtyToReceive <= 0 || !$itemsMap->has($itemId)) {
                    continue;
                }

                $purchaseItem = $itemsMap->get($itemId);
                $remainingAllowed = (float) $purchaseItem->qty - (float) $purchaseItem->qty_received;

                if ($qtyToReceive > ($remainingAllowed + 0.0001)) {
                    throw ValidationException::withMessages([
                        'receiving' => "Cannot receive {$qtyToReceive} for {$purchaseItem->product_name}. Maximum remaining allowed is {$remainingAllowed}.",
                    ]);
                }

                // Increase warehouse stock
                $currentStock = $this->stockTransaction->increaseStock(
                    $purchase->shop_id,
                    $purchaseItem->product_id,
                    $qtyToReceive,
                    $meta['remark'] ?? "Received on {$receipt->receipt_number}"
                );

                // Ensure shop product is active
                $this->stockTransaction->syncShopProduct($purchase->shop_id, $purchaseItem->product_id);

                // Create legacy StockIn record for audit log compatibility
                $stockIn = StockIn::create([
                    'purchase_id'         => $purchase->id,
                    'purchase_receipt_id' => $receipt->id,
                    'product_id'          => $purchaseItem->product_id,
                    'shop_id'             => $purchase->shop_id,
                    'supplier_id'         => $purchase->supplier_id,
                    'supplier_type'       => 'supplier',
                    'qty'                 => $qtyToReceive,
                    'remark'              => $meta['remark'] ?? "Received on {$receipt->receipt_number}",
                    'status'              => 1,
                    'request_by'          => Auth::id() ?? 1,
                    'request_by_type'     => 'admin',
                ]);

                // Create StockHistory record
                $stockHistory = StockHistory::updateOrCreate([
                    'stock_id' => $stockIn->id,
                    'status'   => 'stock_in',
                ], [
                    'product_id'      => $purchaseItem->product_id,
                    'current_stock'   => $currentStock,
                    'stock_in'        => $qtyToReceive,
                    'stock_out'       => 0,
                    'shop_id'         => $purchase->shop_id,
                    'to_id'           => $purchase->supplier_id,
                    'qty'             => $qtyToReceive,
                    'remark'          => $meta['remark'] ?? "Received on {$receipt->receipt_number}",
                    'type'            => 'shop',
                    'request_by'      => Auth::id() ?? 1,
                    'request_by_type' => 'admin',
                ]);

                // Record receipt item
                $receiptItem = new PurchaseReceiptItem();
                $receiptItem->purchase_receipt_id = $receipt->id;
                $receiptItem->purchase_item_id    = $purchaseItem->id;
                $receiptItem->product_id          = $purchaseItem->product_id;
                $receiptItem->qty_received        = $qtyToReceive;
                $receiptItem->stock_history_id    = $stockHistory->id;
                $receiptItem->save();

                // Increment purchase item received quantity
                $purchaseItem->qty_received = round((float) $purchaseItem->qty_received + $qtyToReceive, 2);
                $purchaseItem->save();

                $hasReceivedAny = true;
            }

            if (!$hasReceivedAny) {
                throw ValidationException::withMessages([
                    'receiving' => 'Please enter at least one positive quantity to receive.',
                ]);
            }

            $purchase->recalculateTotals();

            return $receipt;
        });
    }

    /**
     * Receive all remaining quantities across all items for a Purchase Order (Fully Received)
     */
    public function receiveAllRemaining(Purchase $purchase, array $meta = []): PurchaseReceipt
    {
        $purchase->loadMissing(['items.product']);
        $itemsReceiving = [];

        foreach ($purchase->items as $item) {
            $remaining = (float) $item->qty - (float) $item->qty_received;
            if ($remaining > 0.0001) {
                $itemsReceiving[] = [
                    'purchase_item_id' => $item->id,
                    'qty_to_receive'   => $remaining,
                ];
            }
        }

        if (empty($itemsReceiving)) {
            throw ValidationException::withMessages([
                'receiving' => 'All items in this purchase order have already been received.',
            ]);
        }

        return $this->receiveGoods($purchase, $itemsReceiving, $meta);
    }

    /**
     * Record a payment for a Purchase Order
     */
    public function recordPayment(Purchase $purchase, array $data, $receiptFile = null): PurchasePayment
    {
        return DB::transaction(function () use ($purchase, $data, $receiptFile) {
            $amount = (float) ($data['amount'] ?? 0);
            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'Payment amount must be greater than zero.',
                ]);
            }

            $paymentDate = !empty($data['payment_date']) ? Carbon::parse($data['payment_date'])->format('Y-m-d') : date('Y-m-d');

            $payment = new PurchasePayment();
            $payment->purchase_id    = $purchase->id;
            $payment->payment_number = PurchasePayment::generatePaymentNumber($paymentDate);
            $payment->amount         = $amount;
            $payment->payment_date   = $paymentDate;
            $payment->payment_method = $data['payment_method'] ?? 'cash';
            $payment->reference_no   = $data['reference_no'] ?? null;
            $payment->note           = $data['note'] ?? null;
            $payment->created_by     = Auth::id() ?? 1;

            if ($receiptFile) {
                $destination = '/purchase/receipts';
                $uploadedReceipt = UploadFile::saveFile2($destination, $receiptFile);
                $payment->receipt_path = 'uploads' . $destination . '/' . $uploadedReceipt['unique_name'];
                $payment->receipt_name = $uploadedReceipt['original_name'];
            }

            $payment->save();

            $purchase->recalculateTotals();

            return $payment;
        });
    }

    /**
     * Mark a Purchase Order as fully paid (Payment Status: Paid)
     */
    public function markAsPaid(Purchase $purchase, array $data = []): PurchasePayment
    {
        return DB::transaction(function () use ($purchase, $data) {
            $purchase->refresh();
            $remaining = (float) $purchase->remaining_amount;

            if ($remaining <= 0.0001) {
                throw ValidationException::withMessages([
                    'payment' => 'This purchase order has already been paid in full.',
                ]);
            }

            $paymentDate = !empty($data['payment_date']) ? Carbon::parse($data['payment_date'])->format('Y-m-d') : date('Y-m-d');

            $payment = new PurchasePayment();
            $payment->purchase_id    = $purchase->id;
            $payment->payment_number = PurchasePayment::generatePaymentNumber($paymentDate);
            $payment->amount         = $remaining;
            $payment->payment_date   = $paymentDate;
            $payment->payment_method = $data['payment_method'] ?? 'cash';
            $payment->reference_no   = $data['reference_no'] ?? null;
            $payment->note           = $data['note'] ?? 'Full payment recorded from action button';
            $payment->created_by     = Auth::id() ?? 1;
            $payment->save();

            $purchase->recalculateTotals();

            return $payment;
        });
    }

    /**
     * Delete / Void a payment record
     */
    public function deletePayment(PurchasePayment $payment): bool
    {
        return DB::transaction(function () use ($payment) {
            $purchase = $payment->purchase;
            $payment->delete();

            if ($purchase) {
                $purchase->recalculateTotals();
            }

            return true;
        });
    }

    /**
     * Safely delete a Purchase Order, reversing stock if goods were received
     */
    public function deletePurchase(Purchase $purchase): bool
    {
        return DB::transaction(function () use ($purchase) {
            // If goods have been received, reverse stock on hand
            $receipts = $purchase->receipts()->with('items')->get();
            foreach ($receipts as $receipt) {
                foreach ($receipt->items as $receiptItem) {
                    if ((float) $receiptItem->qty_received > 0) {
                        $poLabel = $purchase->purchase_number ?: '#' . $purchase->id;
                        $this->stockTransaction->decreaseStock(
                            $purchase->shop_id,
                            $receiptItem->product_id,
                            $receiptItem->qty_received,
                            "Reversed stock on deletion of PO {$poLabel}"
                        );
                    }
                }
            }

            // Remove associated legacy stock_ins and stock_histories
            $stockIns = StockIn::where('purchase_id', $purchase->id)->get();
            foreach ($stockIns as $si) {
                $this->stockTransaction->deleteHistory($si->id, 'stock_in');
                $si->delete();
            }

            $purchase->items()->delete();
            $purchase->receipts()->delete();
            $purchase->payments()->delete();
            $purchase->delete();

            return true;
        });
    }

    /**
     * Restore a deleted Purchase Order
     */
    public function restorePurchase(Purchase $purchase): bool
    {
        return DB::transaction(function () use ($purchase) {
            $purchase->restore();
            $purchase->items()->restore();
            $purchase->receipts()->restore();
            $purchase->payments()->restore();

            // Re-apply stock if items had been received
            $receipts = $purchase->receipts()->with('items')->get();
            foreach ($receipts as $receipt) {
                foreach ($receipt->items as $receiptItem) {
                    if ((float) $receiptItem->qty_received > 0) {
                        $poLabel = $purchase->purchase_number ?: '#' . $purchase->id;
                        $this->stockTransaction->increaseStock(
                            $purchase->shop_id,
                            $receiptItem->product_id,
                            $receiptItem->qty_received,
                            "Restored stock on restore of PO {$poLabel}"
                        );
                    }
                }
            }

            $purchase->recalculateTotals();

            return true;
        });
    }

    /**
     * Permanently destroy a Purchase Order
     */
    public function forceDeletePurchase(Purchase $purchase): bool
    {
        return DB::transaction(function () use ($purchase) {
            $stockIns = StockIn::withTrashed()->where('purchase_id', $purchase->id)->get();
            foreach ($stockIns as $si) {
                $this->stockTransaction->deleteHistory($si->id, 'stock_in');
                $si->forceDelete();
            }

            $purchase->items()->forceDelete();
            $purchase->receipts()->forceDelete();
            $purchase->payments()->forceDelete();
            $purchase->forceDelete();

            return true;
        });
    }
}
