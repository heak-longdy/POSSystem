<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCurrency extends Model
{
    use HasFactory;
    protected $table = 'user_currencies';
    protected $fillable = [
        'user_id',
        'currency_id',
        'ordering',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }
}
