<?php

namespace App\Services;

use App\Models\InventoryHistory;
use App\Models\Product;
use Carbon\Carbon;
use App\Models\ShopProduct;
use Illuminate\Support\Facades\Auth;

class InventoryService
{
    public function getInventoryStock($shop_id)
    {
        return ShopProduct::where('shop_id',$shop_id)->with('product')->orderByDesc('id')
            ->get();
    }

    public function saveStockHistory($items = [])
    {
        $data = array_merge($items, [
            'history_date' => Carbon::now(),
        ]);
        InventoryHistory::create($data);
    }
}