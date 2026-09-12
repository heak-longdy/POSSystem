<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class StockOut extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'stock_outs';
    protected $fillable = [
        'product_id',
        'shop_id',
        'to_id',
        'qty',
        'remark',
        'type',
        'status',
        'request_by',
        'request_by_type'
    ];
    protected $appends = [
        'created_date',
        'product_title',
        'shop_title',
        'destination_title',
        'category_title',
        'uom_title',
        'request_by_title',
        'stock_status_title',
    ];
    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }
    public function shop()
    {
        return $this->hasOne(Shop::class, 'id', 'shop_id');
    }
    public function customer()
    {
        return $this->hasOne(customer::class, 'id', 'to_id');
    }
    public function stockType()
    {
        return $this->hasOne(StockType::class, 'key', 'to_id');
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
    public function getDestinationTitleAttribute()
    {
        if ($this->type === 'shop') {
            $shop = Shop::find($this->to_id);
            return $shop ? $shop->name : null;
        }

        if ($this->type === 'customer') {
            $customer = Customer::find($this->to_id);
            return $customer ? $customer->name : null;
        }

        if ($this->type === 'stock_type') {
            if ($this->stockType) {
                $stockTypeKey = 'stock_out.stock_types.' . $this->stockType->key;
                $translated = __($stockTypeKey);
                return $translated !== $stockTypeKey ? $translated : $this->stockType->name;
            }
            return null;
        }

        return null;
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
    public function getStockStatusTitleAttribute()
    {
        if ((int) $this->status === 1) {
            return __('stock_out.status.confirmed');
        }

        if ((int) $this->status === 2) {
            return __('stock_out.status.disabled');
        }

        return '---';
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
