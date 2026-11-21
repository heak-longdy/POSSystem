<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::beginTransaction();

        try {
            $rows = DB::table('stock_ins')
                ->whereNull('deleted_at')
                ->whereNull('purchase_id')
                ->orderBy('created_at')
                ->orderBy('id')
                ->get();

            if ($rows->isEmpty()) {
                DB::commit();
                return;
            }

            // Group legacy stock ins by supplier, shop, requester, and minute of creation
            $batches = [];
            foreach ($rows as $r) {
                $minute = substr($r->created_at, 0, 16);
                $key = "{$r->supplier_id}_{$r->shop_id}_{$r->request_by}_{$minute}";
                $batches[$key][] = $r;
            }

            $poCounter = 1;

            foreach ($batches as $key => $items) {
                $first = $items[0];
                $orderDate = substr($first->created_at, 0, 10);
                $dateCode = str_replace('-', '', $orderDate);
                $poNumber = sprintf('PO-%s-%04d', $dateCode, $poCounter++);

                $subtotal = 0;

                foreach ($items as $item) {
                    $product = DB::table('products')->where('id', $item->product_id)->first();
                    $cost = (float) ($product->cost ?? 0);
                    $qty = (float) $item->qty;
                    $subtotal += ($qty * $cost);
                }

                $purchaseId = DB::table('purchases')->insertGetId([
                    'purchase_number'        => $poNumber,
                    'reference_no'           => 'LEGACY-STK-' . $first->id,
                    'supplier_id'            => $first->supplier_id,
                    'shop_id'                => $first->shop_id,
                    'order_date'             => $orderDate,
                    'expected_delivery_date' => $orderDate,
                    'order_status'           => 'received',
                    'payment_status'         => 'paid',
                    'payment_method'         => 'cash',
                    'subtotal'               => $subtotal,
                    'discount_amount'        => 0.00,
                    'tax_amount'             => 0.00,
                    'shipping_cost'          => 0.00,
                    'total_amount'           => $subtotal,
                    'paid_amount'            => $subtotal,
                    'remark'                 => $first->remark ?: 'Migrated from legacy stock in',
                    'created_by'             => $first->request_by,
                    'status'                 => 1,
                    'created_at'             => $first->created_at,
                    'updated_at'             => $first->updated_at,
                ]);

                $receiptId = DB::table('purchase_receipts')->insertGetId([
                    'purchase_id'        => $purchaseId,
                    'receipt_number'     => sprintf('REC-%s-%04d', $dateCode, $poCounter - 1),
                    'reference_no'       => 'LEGACY-STK-' . $first->id,
                    'received_by'        => $first->request_by,
                    'received_date'      => $orderDate,
                    'remark'             => 'Historical receipt auto-generated from legacy stock in',
                    'created_at'         => $first->created_at,
                    'updated_at'         => $first->updated_at,
                ]);

                foreach ($items as $item) {
                    $product = DB::table('products')->where('id', $item->product_id)->first();
                    $cost = (float) ($product->cost ?? 0);
                    $qty = (float) $item->qty;
                    $lineSubtotal = $qty * $cost;

                    $itemId = DB::table('purchase_items')->insertGetId([
                        'purchase_id'  => $purchaseId,
                        'product_id'   => $item->product_id,
                        'qty'          => $qty,
                        'qty_received' => $qty,
                        'unit_cost'    => $cost,
                        'subtotal'     => $lineSubtotal,
                        'remark'       => $item->remark,
                        'created_at'   => $item->created_at,
                        'updated_at'   => $item->updated_at,
                    ]);

                    DB::table('purchase_receipt_items')->insert([
                        'purchase_receipt_id' => $receiptId,
                        'purchase_item_id'    => $itemId,
                        'product_id'          => $item->product_id,
                        'qty_received'        => $qty,
                        'created_at'          => $item->created_at,
                        'updated_at'          => $item->updated_at,
                    ]);

                    DB::table('stock_ins')
                        ->where('id', $item->id)
                        ->update([
                            'purchase_id'         => $purchaseId,
                            'purchase_receipt_id' => $receiptId,
                        ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::beginTransaction();

        try {
            $legacyPurchases = DB::table('purchases')
                ->where('reference_no', 'like', 'LEGACY-STK-%')
                ->pluck('id');

            if ($legacyPurchases->isNotEmpty()) {
                DB::table('stock_ins')
                    ->whereIn('purchase_id', $legacyPurchases)
                    ->update([
                        'purchase_id'         => null,
                        'purchase_receipt_id' => null,
                    ]);

                DB::table('purchase_receipt_items')
                    ->whereIn('purchase_receipt_id', function ($query) use ($legacyPurchases) {
                        $query->select('id')->from('purchase_receipts')->whereIn('purchase_id', $legacyPurchases);
                    })->delete();

                DB::table('purchase_receipts')->whereIn('purchase_id', $legacyPurchases)->delete();
                DB::table('purchase_items')->whereIn('purchase_id', $legacyPurchases)->delete();
                DB::table('purchases')->whereIn('id', $legacyPurchases)->delete();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
};
