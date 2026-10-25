<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'order_details';

    protected $fillable = [
        'order_id',
        'service_id',
        'product_id',
        'price',
        'original_price',
        'unit_price',
        'pricing_method',
        'qty',
        'point',
        'type',
        'product_discount',
        'product_discount_type',
        'service_discount',
        'service_discount_type',
        'service_commission',
        'service_commission_type',
        'product_commission',
        'product_commission_type',
    ];

    protected $casts = [
        'price' => 'double',
        'original_price' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'product_discount' => 'double',
        'service_discount' => 'double',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function shopService()
    {
        return $this->belongsTo(ShopService::class, 'service_id', 'id');
    }
}
