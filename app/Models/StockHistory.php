<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class StockHistory extends Model
{
    use HasFactory;
    protected $table = 'stock_histories';
    protected $fillable = [
        'transfer_id',
        'stock_id',
        'product_id',
        'current_stock',
        'stock_in',
        'stock_out',
        'shop_id',
        'to_id',
        'qty',
        'remark',
        'status',
        'type',
        'transfer_type',
        'request_by',
        'request_by_type',
    ];
    protected $appends = [
        'created_date',
        'product_title',
        'shop_title',
        'category_title',
        'uom_title',
        'stock_status_title',
        'from_title',
        'to_title',
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
    public function getStockStatusTitleAttribute()
    {
        if ($this->status === 'stock_in') {
            return __('stock_movement.status.stock_in');
        }
        if ($this->status === 'stock_out') {
            return __('stock_movement.status.stock_out');
        }
        if ($this->status === 'stock_transfer') {
            return __('stock_movement.status.stock_transfer');
        }

        return ucwords(str_replace('_', ' ', (string) $this->status));
    }
    public function getFromTitleAttribute()
    {
        if ($this->type === 'shop' && $this->status === 'stock_in') {
            $supplier = Supplier::find($this->to_id);
            return $supplier ? $supplier->name : null;
        }

        return $this->shop ? $this->shop->name : null;
    }
    public function getToTitleAttribute()
    {
        if ($this->type === 'shop' && $this->status === 'stock_in') {
            return $this->shop ? $this->shop->name : null;
        }

        if ($this->type === 'shop') {
            $shop = Shop::find($this->to_id);
            return $shop ? $shop->name : null;
        }

        if ($this->type === 'customer') {
            $customer = Customer::find($this->to_id);
            return $customer ? $customer->name : null;
        }

        if ($this->type === 'stock_type') {
            $stockType = StockType::where('key', $this->to_id)->first();
            return $stockType ? $stockType->name : null;
        }

        return null;
    }
    public function getRequestByTitleAttribute()
    {
        if ($this->request_by_type == 'admin') {
            return $this->user ? $this->user->username : null;
        }

        if ($this->request_by_type == 'barber') {
            return $this->barber ? $this->barber->name : null;
        }

        return null;
    }
    public function user()
    {
        return $this->hasOne(User::class, 'id','request_by');
    }
    public function barber()
    {
        return $this->hasOne(Barber::class, 'id', 'request_by');
    }
}
