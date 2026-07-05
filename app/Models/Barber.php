<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Barber extends Authenticatable
{
    use HasFactory, HasApiTokens, SoftDeletes;
    protected $table = 'barbers';
    protected $fillable = ['number_id', 'shop_id', 'name', 'gender', 'dob', 'phone', 'address', 'commission', 'status', 'image', 'password', 'type', 'code', 'wallet'];
    protected $casts = [
        'wallet' => 'double',
    ];

    protected $appends = ['image_url'];
    public function getImageUrlAttribute()
    {
        if ($this->image != null) {
            return url('file_manager' . $this->image);
        }
        return null;
    }
    public function booking()
    {
        return $this->hasMany(Booking::class, 'barber_id', 'id');
    }
    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }
}
