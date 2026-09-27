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
            $path = str_starts_with($this->profile, '/') ? $this->profile : '/' . $this->profile;
            return url('file_manager' . $path);
        }
        return null;
    }
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id', 'id');
    }
}
