<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'booking_payments';

    protected $fillable = [
        'booking_id',
        'amount',
        'payment_method',
        'payment_date',
        'note',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'double',
        'payment_date' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
