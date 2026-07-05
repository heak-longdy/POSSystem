<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Slide extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'slides';
    protected $fillable = ['name','ordering', 'status', 'image'];
    protected $appends = ['image_url'];
    public function getImageUrlAttribute()
    {
        if ($this->image != null) {
            return url('file_manager' . $this->image);
        }
        return null;
    }
    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at'
    ];
}
