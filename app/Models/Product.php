<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $table = 'products';
    protected $fillable = ['category_id', 'uom_id', 'name', 'price', 'image', 'status', 'commission'];
    protected $casts = [
        'price' => 'double',
        'commission' => 'double'
    ];
    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        if ($this->image != null) {
            return url('file_manager' . $this->image);
        }
        return null;
    }
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }
    public function uom()
    {
        return $this->belongsTo(UOM::class, 'uom_id', 'id');
    }
    public function stockIn()
    {
        return $this->hasOne(StockIn::class, 'product_id', 'id');
    }
}
