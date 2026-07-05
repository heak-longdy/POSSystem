<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;
    protected $table = 'promotions';
    protected $fillable = [
        'title',
        'description',
        'customer_id',
        'shop_id', 
        'product_id', 
        'service_id',
        'discount',
        'type',
        'from_date',
        'to_date'
    ];

    protected $casts = [
        'product_id' => 'array',
        'service_id' => 'array',
        'customer_id'=> 'array',
        'shop_id'    => 'array',
    ];

}
