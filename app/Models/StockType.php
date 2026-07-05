<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockType extends Model
{
    use HasFactory;
    protected $table = 'stock_types';
    protected $fillable = [
        'key',
        'name',
        'ordering',
        'user_id',
        'status'
    ];
}
