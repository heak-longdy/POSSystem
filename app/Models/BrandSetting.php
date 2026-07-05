<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandSetting extends Model
{
    use HasFactory;
    protected $table = 'brand_settings';
    protected $fillable = ['brand_id','brand_point_use','status'];
    protected $casts = [
        'brand_point_use' => 'array',
    ];
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'id');
    }
}
