<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ShopProduct;
use App\Models\StockHistory;
use App\Models\StockIn;
use App\Models\StockOnHand;
use App\Models\StockOut;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class StockTransactionService
{
    public function applyStockIn(StockIn $stockIn)
    {
        $currentStock = $this->increaseStock($stockIn->shop_id, $stockIn->product_id, $stockIn->qty, $stockIn->remark);

        StockHistory::updateOrCreate([
            'stock_id' => $stockIn->id,
            'status' => 'stock_in',
        ], [
            'product_id' => $stockIn->product_id,
            'current_stock' => $currentStock,
            'stock_in' => $stockIn->qty,
            'stock_out' => 0,
            'shop_id' => $stockIn->shop_id,
            'to_id' => $stockIn->supplier_id,
            'qty' => $stockIn->qty,
            'remark' => $stockIn->remark,
            'type' => 'shop',
            'request_by' => $stockIn->request_by,
            'request_by_type' => $stockIn->request_by_type,
        ]);
    }

    public function reverseStockIn(StockIn $stockIn)
    {
        $this->decreaseStock($stockIn->shop_id, $stockIn->product_id, $stockIn->qty, $stockIn->remark);
        $this->deleteHistory($stockIn->id, 'stock_in');
    }

    public function applyStockOut(StockOut $stockOut)
    {
        $currentStock = $this->decreaseStock($stockOut->shop_id, $stockOut->product_id, $stockOut->qty, $stockOut->remark);

        StockHistory::updateOrCreate([
            'stock_id' => $stockOut->id,
            'status' => 'stock_out',
        ], [
            'product_id' => $stockOut->product_id,
            'current_stock' => $currentStock,
            'stock_in' => 0,
            'stock_out' => $stockOut->qty,
            'shop_id' => $stockOut->shop_id,
            'to_id' => $stockOut->to_id,
            'qty' => $stockOut->qty,
            'remark' => $stockOut->remark,
            'type' => $stockOut->type ?: 'stock_type',
            'request_by' => $stockOut->request_by,
            'request_by_type' => $stockOut->request_by_type,
        ]);
    }

    public function reverseStockOut(StockOut $stockOut)
    {
        $this->increaseStock($stockOut->shop_id, $stockOut->product_id, $stockOut->qty, $stockOut->remark);
        $this->deleteHistory($stockOut->id, 'stock_out');
    }

    public function applyStockTransfer(StockTransfer $stockTransfer)
    {
        $currentStock = $this->decreaseStock(
            $stockTransfer->from_shop_id,
            $stockTransfer->product_id,
            $stockTransfer->qty,
            $stockTransfer->remark
        );

        $this->increaseStock(
            $stockTransfer->to_shop_id,
            $stockTransfer->product_id,
            $stockTransfer->qty,
            $stockTransfer->remark
        );

        $this->syncShopProduct($stockTransfer->to_shop_id, $stockTransfer->product_id);

        StockHistory::updateOrCreate([
            'stock_id' => $stockTransfer->id,
            'status' => 'stock_transfer',
        ], [
            'product_id' => $stockTransfer->product_id,
            'current_stock' => $currentStock,
            'stock_in' => 0,
            'stock_out' => $stockTransfer->qty,
            'shop_id' => $stockTransfer->from_shop_id,
            'to_id' => $stockTransfer->to_shop_id,
            'qty' => $stockTransfer->qty,
            'remark' => $stockTransfer->remark,
            'type' => 'shop',
            'request_by' => $stockTransfer->request_by,
            'request_by_type' => $stockTransfer->request_by_type,
        ]);
    }

    public function reverseStockTransfer(StockTransfer $stockTransfer)
    {
        $this->increaseStock(
            $stockTransfer->from_shop_id,
            $stockTransfer->product_id,
            $stockTransfer->qty,
            $stockTransfer->remark
        );

        $this->decreaseStock(
            $stockTransfer->to_shop_id,
            $stockTransfer->product_id,
            $stockTransfer->qty,
            $stockTransfer->remark
        );

        $this->deleteHistory($stockTransfer->id, 'stock_transfer');
    }

    public function increaseStock($shopId, $productId, $qty, $remark = null)
    {
        $stockOnHand = StockOnHand::where('shop_id', $shopId)
            ->where('product_id', $productId)
            ->lockForUpdate()
            ->first();

        if ($stockOnHand) {
            $stockOnHand->update([
                'current_stock' => (int) $stockOnHand->current_stock + (int) $qty,
                'remark' => $remark,
                'request_by' => Auth::id(),
                'request_by_type' => 'admin',
            ]);
        } else {
            $stockOnHand = StockOnHand::create([
                'shop_id' => $shopId,
                'product_id' => $productId,
                'current_stock' => (int) $qty,
                'remark' => $remark,
                'status' => 1,
                'request_by' => Auth::id(),
                'request_by_type' => 'admin',
            ]);
        }

        return (int) $stockOnHand->current_stock;
    }

    public function decreaseStock($shopId, $productId, $qty, $remark = null)
    {
        $stockOnHand = StockOnHand::where('shop_id', $shopId)
            ->where('product_id', $productId)
            ->lockForUpdate()
            ->first();

        if (!$stockOnHand || (int) $stockOnHand->current_stock < (int) $qty) {
            throw ValidationException::withMessages([
                'qty' => 'Qty is limited or out of stock',
            ]);
        }

        $stockOnHand->update([
            'current_stock' => (int) $stockOnHand->current_stock - (int) $qty,
            'remark' => $remark,
            'request_by' => Auth::id(),
            'request_by_type' => 'admin',
        ]);

        return (int) $stockOnHand->current_stock;
    }

    public function deleteHistory($stockId, $status)
    {
        StockHistory::where('stock_id', $stockId)
            ->where('status', $status)
            ->delete();
    }

    public function syncShopProduct($shopId, $productId)
    {
        $product = Product::find($productId);
        if (!$product) {
            return;
        }

        $shopProduct = ShopProduct::withTrashed()
            ->where('shop_id', $shopId)
            ->where('product_id', $productId)
            ->first();

        if ($shopProduct) {
            if ($shopProduct->trashed()) {
                $shopProduct->restore();
            }

            $shopProduct->update(['status' => 1]);
            return;
        }

        ShopProduct::create([
            'shop_id' => $shopId,
            'product_id' => $productId,
            'promotion_id' => null,
            'price' => $product->price,
            'max_qty' => 0,
            'status' => 1,
            'commission_type' => 'usd',
            'commission' => 0,
        ]);
    }
}
