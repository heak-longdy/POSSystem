<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerReward extends Model
{
    use HasFactory;
    protected $table = 'customer_rewards';
    protected $fillable = ['customer_id','amount', 'used_date', 'name'];

}
