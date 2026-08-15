<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Position extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'positions';
    protected $fillable = ['image', 'title', 'status', 'order', 'user'];

    public function staff()
    {
        return $this->hasMany(Staff::class, 'position_id');
    }
}