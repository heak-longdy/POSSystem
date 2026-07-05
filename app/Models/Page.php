<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;
    protected $table = 'pages';
    protected $fillable = ['title', 'type', 'content', 'status'];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected $appends = ['image_url'];
    public function getImageUrlAttribute()
    {
        if ($this->cover != null) {
            return url('file_manager' . $this->cover);
        }
        return null;
    }
}
