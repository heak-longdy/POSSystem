<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerPaid extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'customer_paid';
    protected $fillable = [
        'customer_id',
        'customer_name',
        'currency',
        'amount_usd',
        'amount_kh',
        'des',
        'user',
        'status'
    ];
    protected $appends = ['customer_title'];
        public function Customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
    public function getCustomerTitleAttribute()
    {
        if ($this->Customer()) {
            return $this->Customer?->name ?? "";
        }
    }
    public function getAmountKhForAttribute()
    {
        return $this->amount_kh ? number_format($this->amount_kh ?? 0, 0) . ' ៛' : '';
    }
    public function getAmountUsdForAttribute()
    {
        return $this->amount_usd ? number_format($this->amount_usd ?? 0, 2) . ' $' : '';
    }

    public function setAmountUsdAttribute($value)
    {
        if ($value === null || $value === '') {
            $this->attributes['amount_usd'] = null;
            return;
        }
        $this->attributes['amount_usd'] = str_replace(',', '', $value);
    }

    public function setAmountKhAttribute($value)
    {
        if ($value === null || $value === '') {
            $this->attributes['amount_kh'] = null;
            return;
        }
        $this->attributes['amount_kh'] = str_replace(',', '', $value);
    }
}
