<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointHistory extends Model
{
    use HasFactory;
    protected $table = 'point_histories';
    protected $fillable = ['customer_id','name', 'amount','des'];

}
