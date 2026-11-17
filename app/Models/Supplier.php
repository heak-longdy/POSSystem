<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'suppliers';

    protected $fillable = [
        'code',
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'tax_number',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'notes',
        'ordering',
        'image',
        'user_id',
        'status',
    ];

    protected $appends = [
        'image_url',
    ];

    public function getImageUrlAttribute()
    {
        if ($this->image != null) {
            return url('file_manager' . $this->image);
        }
        return null;
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'supplier_id');
    }

    public function purchasePayments()
    {
        return $this->hasMany(PurchasePayment::class, 'supplier_id');
    }

    public function stockIns()
    {
        return $this->hasMany(StockIn::class, 'supplier_id');
    }
}
