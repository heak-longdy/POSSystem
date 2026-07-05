<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    use HasFactory;
    protected $table = 'rewards';
    protected $fillable = ['name','price', 'des','status','start_date','end_date','product_id','service_id'];
    protected $casts = [
        'product_id' => 'array',
        'service_id' => 'array',
    ];
}
