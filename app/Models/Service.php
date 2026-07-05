<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;
    protected $table = 'services';
    protected $fillable = ['name', 'price','status','image','ordering','commission'];
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
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    
}
