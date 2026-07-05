<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointSetting extends Model
{
    use HasFactory;
    protected $table = 'point_settings';
    protected $fillable = ['brand_id','discount','discount_type','used_count','from_date','to_date','status'];
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'id');
    }
}
