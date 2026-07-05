<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;
    protected $table = 'discounts';
    protected $fillable = ['customer_id','type', 'discount', 'start_date','end_date'];

    protected $casts = [
        'customer_id' => 'array',
    ];
}
