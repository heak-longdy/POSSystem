<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'orders';

    protected $fillable = [
        'customer_id',
        'total_price',
        'total_commission',
        'total_discount',
        'paid_amount',
        'shop_id',
        'barber_id',
        'order_date',
        'delivery_date',
        'payment_status',
        'payment_date',
        'invoice_number',
        'pay_way',
        'total_point',
        'remark',
    ];

    protected $casts = [
        'total_price' => 'double',
        'total_discount' => 'double',
        'total_commission' => 'double',
        'paid_amount' => 'double',
        'order_date' => 'datetime',
        'delivery_date' => 'datetime',
        'payment_date' => 'datetime',
    ];

    protected $appends = ['remaining_amount'];

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

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'order_id');
    }

    public function payments()
    {
        return $this->hasMany(OrderPayment::class, 'order_id')
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc');
    }

    public function getRemainingAmountAttribute()
    {
        return max(0, (float) ($this->total_price ?? 0) - (float) ($this->paid_amount ?? 0));
    }
}
