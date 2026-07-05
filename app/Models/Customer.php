<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'customers';
    protected $fillable = ['name', 'ordering', 'phone', 'address', 'profile', 'password', 'status', 'total_point','user'];

    protected $appends = ['image_url'];
    public function getImageUrlAttribute()
    {
        if ($this->profile != null) {
            return url('file_manager' . $this->profile);
        }
        return null;
    }
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
