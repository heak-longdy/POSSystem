<?php

namespace App\Services;

use App\Models\InvoiceSequence;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    protected StockTransactionService $stockTransaction;

    public function __construct(StockTransactionService $stockTransaction)
    {
        $this->stockTransaction = $stockTransaction;
    }

    /**
     * Generate the next unique PO number in a concurrency-safe atomic transaction.
     */
    public function generateNextPurchaseNumber(?Carbon $date = null): string
    {
        $date = $date ?? now();
        $prefixKey = 'PUR-' . $date->format('Ym') . '-';
        $digitLength = 4;
        $startNumber = 1;

        return DB::transaction(function () use ($prefixKey, $digitLength, $startNumber) {
            if (!Schema::hasTable('invoice_sequences')) {
                return $this->fallbackGenerateNext($prefixKey, $digitLength, $startNumber);
            }

            $sequence = InvoiceSequence::where('prefix_key', $prefixKey)
                ->lockForUpdate()
                ->first();

            if (!$sequence) {
                $maxExisting = $this->detectMaxExistingNumber($prefixKey);
                $initialNumber = max($maxExisting, $startNumber - 1);

                $sequence = InvoiceSequence::create([
                    'prefix_key'  => $prefixKey,
                    'last_number' => $initialNumber,
                ]);
            }

            $nextNumber = $sequence->last_number + 1;
            $sequence->update(['last_number' => $nextNumber]);

            $paddedNumber = str_pad((string) $nextNumber, $digitLength, '0', STR_PAD_LEFT);

            return $prefixKey . $paddedNumber;
        }, 3);
    }

    private function detectMaxExistingNumber(string $prefixKey): int
    {
        if (!Schema::hasTable('purchases')) {
            return 0;
        }

        $latest = Purchase::withTrashed()
            ->where('purchase_number', 'LIKE', $prefixKey . '%')
            ->orderByRaw('LENGTH(purchase_number) DESC')
            ->orderBy('purchase_number', 'desc')
            ->value('purchase_number');

        if (!$latest) {
            return 0;
        }

        $suffix = substr($latest, strlen($prefixKey));
        return is_numeric($suffix) ? (int) $suffix : 0;
    }

    private function fallbackGenerateNext(string $prefixKey, int $digitLength, int $startNumber): string
    {
        $maxExisting = $this->detectMaxExistingNumber($prefixKey);
        $nextNumber = max($maxExisting + 1, $startNumber);
        return $prefixKey . str_pad((string) $nextNumber, $digitLength, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate financial totals for line items and overall purchase order.
     */
    public function calculateTotals(
        array $items,
        string $discountType = 'fixed',
        float $discountRate = 0.0,
        float $discountAmount = 0.0,
        float $taxRate = 0.0,
        float $shippingCost = 0.0
    ): array {
        $subtotal = 0.0;
        $calculatedItems = [];

        foreach ($items as $item) {
            $orderedQty = max(0.01, (float) ($item['ordered_qty'] ?? 1.0));
            $unitCost = max(0.0, (float) ($item['unit_cost'] ?? 0.0));
            $itemDiscount = max(0.0, (float) ($item['discount_amount'] ?? 0.0));
            $itemTax = max(0.0, (float) ($item['tax_amount'] ?? 0.0));

            $lineTotal = round(($orderedQty * $unitCost) - $itemDiscount + $itemTax, 2);
            $subtotal += $lineTotal;

            $calculatedItems[] = array_merge($item, [
                'ordered_qty'     => $orderedQty,
                'unit_cost'       => $unitCost,
                'discount_amount' => $itemDiscount,
                'tax_amount'      => $itemTax,
                'subtotal'        => $lineTotal,
            ]);
        }

        $subtotal = round($subtotal, 2);

        // Overall order discount
        if ($discountType === 'percentage') {
            $discountAmount = round(($subtotal * max(0.0, min(100.0, $discountRate))) / 100.0, 2);
        } else {
            $discountAmount = round(max(0.0, $discountAmount), 2);
        }

        $taxableBase = max(0.0, $subtotal - $discountAmount);
        $taxAmount = round(($taxableBase * max(0.0, min(100.0, $taxRate))) / 100.0, 2);
        $shippingCost = round(max(0.0, $shippingCost), 2);

        $totalAmount = max(0.0, round($subtotal - $discountAmount + $taxAmount + $shippingCost, 2));

        return [
            'subtotal'        => $subtotal,
            'discount_type'   => $discountType,
            'discount_rate'   => $discountRate,
            'discount_amount' => $discountAmount,
            'tax_rate'        => $taxRate,
            'tax_amount'      => $taxAmount,
            'shipping_cost'   => $shippingCost,
            'total_amount'    => $totalAmount,
            'items'           => $calculatedItems,
        ];
    }

    /**
     * Create a new Purchase order.
     */
    public function createPurchase(array $attributes, array $rawItems): Purchase
    {
        return DB::transaction(function () use ($attributes, $rawItems) {
            $purchaseNumber = !empty($attributes['purchase_number'])
                ? $attributes['purchase_number']
                : $this->generateNextPurchaseNumber(
                    !empty($attributes['purchase_date']) ? Carbon::parse($attributes['purchase_date']) : null
                );

            $totals = $this->calculateTotals(
                $rawItems,
                $attributes['discount_type'] ?? 'fixed',
                (float) ($attributes['discount_rate'] ?? 0.0),
                (float) ($attributes['discount_amount'] ?? 0.0),
                (float) ($attributes['tax_rate'] ?? 0.0),
                (float) ($attributes['shipping_cost'] ?? 0.0)
            );

            $status = $attributes['status'] ?? 'draft';

            $purchase = Purchase::create([
                'purchase_number'      => $purchaseNumber,
                'supplier_invoice_no'  => $attributes['supplier_invoice_no'] ?? null,
                'supplier_id'          => $attributes['supplier_id'],
                'shop_id'              => $attributes['shop_id'],
                'purchase_date'        => $attributes['purchase_date'] ?? now()->toDateString(),
                'delivery_date'        => $attributes['delivery_date'] ?? null,
                'status'               => $status,
                'payment_status'       => 'unpaid',
                'payment_due_date'     => $attributes['payment_due_date'] ?? null,
                'subtotal'             => $totals['subtotal'],
                'discount_type'        => $totals['discount_type'],
                'discount_rate'        => $totals['discount_rate'],
                'discount_amount'      => $totals['discount_amount'],
                'tax_rate'             => $totals['tax_rate'],
                'tax_amount'           => $totals['tax_amount'],
                'shipping_cost'        => $totals['shipping_cost'],
                'total_amount'         => $totals['total_amount'],
                'paid_amount'          => 0.00,
                'creation_request_key' => $attributes['creation_request_key'] ?? null,
                'remark'               => $attributes['remark'] ?? null,
                'created_by'           => Auth::id() ?? 1,
            ]);

            foreach ($totals['items'] as $itemData) {
                PurchaseItem::create([
                    'purchase_id'         => $purchase->id,
                    'product_id'          => $itemData['product_id'],
                    'uom_id'              => $itemData['uom_id'] ?? Product::find($itemData['product_id'])?->uom_id,
                    'ordered_qty'         => $itemData['ordered_qty'],
                    'received_qty'        => 0.00,
                    'unit_cost'           => $itemData['unit_cost'],
                    'discount_amount'     => $itemData['discount_amount'],
                    'tax_amount'          => $itemData['tax_amount'],
                    'subtotal'            => $itemData['subtotal'],
                    'update_product_cost' => !empty($itemData['update_product_cost']),
                    'remark'              => $itemData['remark'] ?? null,
                ]);
            }

            if ($status === 'received') {
                $this->receivePurchase($purchase);
            }

            return $purchase->fresh(['items.product', 'supplier', 'shop']);
        });
    }

    /**
     * Update an existing Purchase order.
     */
    public function updatePurchase(Purchase $purchase, array $attributes, array $rawItems): Purchase
    {
        return DB::transaction(function () use ($purchase, $attributes, $rawItems) {
            if ($purchase->status === 'received') {
                throw ValidationException::withMessages([
                    'status' => 'Received purchase orders cannot be modified. Revert goods receipt or cancel instead.',
                ]);
            }

            $totals = $this->calculateTotals(
                $rawItems,
                $attributes['discount_type'] ?? $purchase->discount_type,
                (float) ($attributes['discount_rate'] ?? $purchase->discount_rate),
                (float) ($attributes['discount_amount'] ?? $purchase->discount_amount),
                (float) ($attributes['tax_rate'] ?? $purchase->tax_rate),
                (float) ($attributes['shipping_cost'] ?? $purchase->shipping_cost)
            );

            $purchase->update([
                'supplier_invoice_no' => $attributes['supplier_invoice_no'] ?? $purchase->supplier_invoice_no,
                'supplier_id'         => $attributes['supplier_id'] ?? $purchase->supplier_id,
                'shop_id'             => $attributes['shop_id'] ?? $purchase->shop_id,
                'purchase_date'       => $attributes['purchase_date'] ?? $purchase->purchase_date,
                'delivery_date'       => $attributes['delivery_date'] ?? $purchase->delivery_date,
                'status'              => $attributes['status'] ?? $purchase->status,
                'payment_due_date'    => $attributes['payment_due_date'] ?? $purchase->payment_due_date,
                'subtotal'            => $totals['subtotal'],
                'discount_type'       => $totals['discount_type'],
                'discount_rate'       => $totals['discount_rate'],
                'discount_amount'     => $totals['discount_amount'],
                'tax_rate'            => $totals['tax_rate'],
                'tax_amount'          => $totals['tax_amount'],
                'shipping_cost'       => $totals['shipping_cost'],
                'total_amount'        => $totals['total_amount'],
                'remark'              => $attributes['remark'] ?? $purchase->remark,
            ]);

            // Sync line items: delete old items and recreate
            $purchase->items()->delete();

            foreach ($totals['items'] as $itemData) {
                PurchaseItem::create([
                    'purchase_id'         => $purchase->id,
                    'product_id'          => $itemData['product_id'],
                    'uom_id'              => $itemData['uom_id'] ?? Product::find($itemData['product_id'])?->uom_id,
                    'ordered_qty'         => $itemData['ordered_qty'],
                    'received_qty'        => 0.00,
                    'unit_cost'           => $itemData['unit_cost'],
                    'discount_amount'     => $itemData['discount_amount'],
                    'tax_amount'          => $itemData['tax_amount'],
                    'subtotal'            => $itemData['subtotal'],
                    'update_product_cost' => !empty($itemData['update_product_cost']),
                    'remark'              => $itemData['remark'] ?? null,
                ]);
            }

            if (($attributes['status'] ?? null) === 'received') {
                $this->receivePurchase($purchase);
            }

            return $purchase->fresh(['items.product', 'supplier', 'shop']);
        });
    }

    /**
     * Mark goods received and increase stock on hand.
     */
    public function receivePurchase(Purchase $purchase, array $receivedQuantities = []): Purchase
    {
        return DB::transaction(function () use ($purchase, $receivedQuantities) {
            if ($purchase->status === 'cancelled') {
                throw ValidationException::withMessages([
                    'status' => 'Cannot receive goods for a cancelled purchase order.',
                ]);
            }

            $this->stockTransaction->applyPurchaseReceive($purchase, $receivedQuantities);

            // Check if all items are fully received
            $purchase->refresh();
            $allReceived = true;
            $anyReceived = false;

            foreach ($purchase->items as $item) {
                if ((float) $item->received_qty < (float) $item->ordered_qty) {
                    $allReceived = false;
                }
                if ((float) $item->received_qty > 0) {
                    $anyReceived = true;
                }
            }

            $newStatus = $allReceived ? 'received' : ($anyReceived ? 'partial_received' : $purchase->status);

            $purchase->update([
                'status'      => $newStatus,
                'received_by' => Auth::id() ?? $purchase->received_by,
                'received_at' => now(),
            ]);

            return $purchase->fresh(['items.product', 'supplier', 'shop']);
        });
    }

    /**
     * Cancel a purchase order, safely reversing stock if goods were already received.
     */
    public function cancelPurchase(Purchase $purchase): Purchase
    {
        return DB::transaction(function () use ($purchase) {
            if ($purchase->status === 'cancelled') {
                return $purchase;
            }

            if (in_array($purchase->status, ['received', 'partial_received'], true)) {
                $this->stockTransaction->reversePurchaseReceive($purchase);
            }

            $purchase->update([
                'status' => 'cancelled',
            ]);

            return $purchase->fresh();
        });
    }
}
