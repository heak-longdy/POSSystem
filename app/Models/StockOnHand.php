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
    ];
    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }
    public function shop()
    {
        return $this->hasOne(Shop::class, 'id', 'shop_id');
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
        if ($this->request_by_type === 'admin') {
            return $this->user ? $this->user->username : null;
        }

        if ($this->request_by_type === 'barber') {
            return $this->barber ? $this->barber->name : null;
        }

        return null;
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
