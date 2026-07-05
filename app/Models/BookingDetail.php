<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingDetail extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'booking_details';
    // protected $fillable = ['booking_id', 'service_id', 'product_id', 'price', 'qty', 'type', 'product_discount', 'product_discount_type', 'service_discount', 'service_discount_type', 'discount', 'commission', 'rate'];
    protected $fillable = [
        'booking_id',
        'service_id',
        'product_id',
        'price',
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
        'product_discount' => 'double',
        'service_discount' => 'double',
    ];
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
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
