<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class StockOnHand extends Model
{
    use HasFactory;
    protected $table = 'stock_on_hands';
    protected $fillable = [
        'product_id',
        'shop_id',
        'current_stock',
        'remark',
        'status',
        'request_by',
        'request_by_type'
    ];
    protected $appends = [
        'created_date',
        'product_title',
        'shop_title',
        'category_title',
        'uom_title',
        'request_by_title',
        'total_cost',
        'total_cost_title',
        'total_amount',
        'total_amount_title',
        'unit_cost',
        'unit_cost_title',
        'unit_price',
        'unit_price_title',
    ];
    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }
    public function shop()
    {
        return $this->hasOne(Shop::class, 'id', 'shop_id');
    }
    public function shopProducts()
    {
        return $this->hasMany(ShopProduct::class, 'product_id', 'product_id');
    }
    public function getCreatedDateAttribute()
    {
        return $this->created_at ? Carbon::parse($this->created_at)->format('d/M/Y h:i A') : null;
    }
    public function getProductTitleAttribute()
    {
        return $this->product ? $this->product->name : null;
    }
    public function getShopTitleAttribute()
    {
        return $this->shop ? $this->shop->name : null;
    }
    public function getCategoryTitleAttribute()
    {
        return $this->product && $this->product->category ? $this->product->category->name : null;
    }
    public function getUomTitleAttribute()
    {
        return $this->product && $this->product->uom ? $this->product->uom->name : null;
    }
    public function getRequestByTitleAttribute()
    {
        return $this->user ? $this->user->name : null;
    }
    public function getUnitCostAttribute()
    {
        return (float) ($this->product ? $this->product->cost : 0.0);
    }
    public function getUnitCostTitleAttribute()
    {
        $val = $this->unit_cost;
        return $val < 0 ? '-$' . number_format(abs($val), 2) : '$' . number_format($val, 2);
    }
    public function getUnitPriceAttribute()
    {
        if ($this->relationLoaded('shopProducts') && $this->shopProducts) {
            $sp = $this->shopProducts->firstWhere('shop_id', $this->shop_id);
            if ($sp && $sp->price !== null && $sp->price > 0) {
                return (float) $sp->price;
            }
        } else {
            $sp = ShopProduct::where('shop_id', $this->shop_id)->where('product_id', $this->product_id)->first();
            if ($sp && $sp->price !== null && $sp->price > 0) {
                return (float) $sp->price;
            }
        }
        return (float) ($this->product ? $this->product->price : 0.0);
    }
    public function getUnitPriceTitleAttribute()
    {
        $val = $this->unit_price;
        return $val < 0 ? '-$' . number_format(abs($val), 2) : '$' . number_format($val, 2);
    }
    public function getTotalCostAttribute()
    {
        return (float) (($this->current_stock ?? 0) * $this->unit_cost);
    }
    public function getTotalCostTitleAttribute()
    {
        $val = $this->total_cost;
        return $val < 0 ? '-$' . number_format(abs($val), 2) : '$' . number_format($val, 2);
    }
    public function getTotalAmountAttribute()
    {
        return (float) (($this->current_stock ?? 0) * $this->unit_price);
    }
    public function getTotalAmountTitleAttribute()
    {
        $val = $this->total_amount;
        return $val < 0 ? '-$' . number_format(abs($val), 2) : '$' . number_format($val, 2);
    }
    public function getTotalPriceAttribute()
    {
        return $this->total_amount;
    }
    public function getTotalPriceTitleAttribute()
    {
        return $this->total_amount_title;
    }
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'request_by');
    }
    public function barber()
    {
        return $this->hasOne(Barber::class, 'id', 'request_by');
    }
}
