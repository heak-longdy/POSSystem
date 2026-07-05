<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
class Shop extends Authenticatable
{
    use HasFactory,HasApiTokens;
    protected $table = 'shops';
    protected $fillable = ['type_id','name', 'nick_name', 'address','status','total_wallet','phone','image','password','barber_id'];

    protected $casts = [
        'product_id' => 'array',
    ];
    public function booking()
    {
        $today = date('Y-m-d');
        $fromDate = request('from_date');
        $toDate = request('to_date');
        $id = request('id');
        return $this->hasMany(Booking::class, 'shop_id', 'id')
            ->where(function($q) use ($fromDate, $toDate, $id, $today) {
                if ($fromDate && $toDate && $id) {
                    $q->whereDate('booking_date', '>=', $fromDate);
                    $q->whereDate('booking_date', '<=', $toDate);
                    $q->where('shop_id', (int)$id);
                } else if ($fromDate && $toDate && !$id) {
                    $q->whereDate('booking_date', '>=', $fromDate);
                    $q->whereDate('booking_date', '<=', $toDate);
                } else if ($id) {
                    $q->where('shop_id', $id);
                } else {
                    $q->whereDate('booking_date', $today);
                }
            });
    }
    
    protected $appends = [
        'image_url',
        'total_amount',
        'total_commission',
    ];

    public function getTotalAmountAttribute()
    {
        return $this->booking()->sum('total_price');
    }

    public function getTotalCommissionAttribute()
    {
        return $this->booking()->sum('total_commission');
        return $this->booking()->sum('commission');
    }
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'id');
    }
    public function getImageUrlAttribute()
    {
        if ($this->image != null) {
            return url('file_manager' . $this->image);
        }
        return null;
    }
    
}
