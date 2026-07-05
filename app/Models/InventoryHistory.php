<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryHistory extends Model
{
    use HasFactory;
    protected $table = 'inventory_histories';
    protected $fillable = [
        'shop_id',
        'shop_product_id',
        'start_qty',
        'end_qty',
        'stock_qty',
        'remark',
        'history_date',
        'type',
    ];

    public function product()
    {
        return $this->belongsTo(ShopProduct::class, 'product_id');
    }
    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }
}
