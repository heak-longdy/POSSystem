<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $table = 'products';
    protected $fillable = [
                            'category_id', 
                            'uom_id', 
                            'name',
                            'cost', 
                            'price', 
                            'image', 
                            'status',
                            'commission'
                        ];
    protected $casts = [
        'cost' => 'double',
        'price' => 'double',
        'commission' => 'double'
    ];

    protected $appends = [
        'image_url', 
        'category_title',
        'uom_title',
        'PriceUsdFor',
        'CostUsdFor'
    ];
    
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
    public function getCategoryTitleAttribute()
    {
        return $this->category ? $this->category->name : null;
    }
    public function getUomTitleAttribute()
    {
        return $this->uom ? $this->uom->name : null;
    }
    public function getCostUsdForAttribute()
    {
        return $this->cost ? number_format($this->cost ?? 0, 2) . ' $' : '';
    }
    public function setCostAttribute($value=null)
    {
        if ($value === null || $value === '') {
            $this->attributes['cost'] = null;
            return;
        }
        $this->attributes['cost'] = str_replace(',', '', $value);
    }
    public function getPriceUsdForAttribute()
    {
        return $this->price ? number_format($this->price ?? 0, 2) . ' $' : '';
    }
    public function setPriceAttribute($value=null)
    {
        if ($value === null || $value === '') {
            $this->attributes['price'] = null;
            return;
        }
        $this->attributes['price'] = str_replace(',', '', $value);
    }
}
