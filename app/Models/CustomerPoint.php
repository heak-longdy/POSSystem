<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPoint extends Model
{
    use HasFactory;
    protected $table = 'customer_points';
    protected $fillable = [
        'customer_id',
        'shop_id',
        'brand_id',
        'total_point',
        'total_receving_point',
        'used_point',
        'count_of_using_service'
    ];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }
}
