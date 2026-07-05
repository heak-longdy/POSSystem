<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'bookings';
    protected $fillable = [
        'customer_id',
        'total_price',
        'total_commission',
        'total_discount',
        'shop_id',
        'payment_status',
        'invoice_number',
        'total_point',
        'remark'
    ];

    protected $casts = [
        'total_price' => 'double',
        'total_discount' => 'double',
        'total_commission' => 'double',
    ];
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id', 'id');
    }
    public function barber()
    {
        return $this->belongsTo(Barber::class, 'barber_id');
    }
    public function bookingDetail(){
        
        return $this->hasMany(BookingDetail::class, 'booking_id');
    }
}
