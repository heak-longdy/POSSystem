<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopProduct extends Model
{
    use HasFactory;
    protected $table = 'shop_products';
    protected $fillable = [
        'shop_id',
        'product_id',
        'price',
        'point',
        'max_qty',
        'commission',
        'commission_type',
        'status',
        'promotion_id'
    ];
    protected $casts = [
        'price' => 'double',
        'discount' => 'double',
        'commission' => 'double'
    ];
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
