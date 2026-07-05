<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;
    protected $table = 'countries';
    protected $fillable = ['name','code','flag','status'];
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    protected $appends = ['image_url'];
    public function getImageUrlAttribute()
    {
        if ($this->flag != null) {
            return url('file_manager' . $this->flag);
        }
        return null;
    }
}
