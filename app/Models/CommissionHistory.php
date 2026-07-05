<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionHistory extends Model
{
    use HasFactory;
    protected $table = 'commission_histories';
    protected $fillable = ['shop_id','barber_id','amount', 'des'];
}
