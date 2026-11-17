<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchasePayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'purchase_payments';

    protected $fillable = [
        'purchase_id',
        'supplier_id',
        'payment_number',
        'amount',
        'payment_date',
        'payment_method',
        'reference_no',
        'receipt_path',
        'receipt_name',
        'note',
        'created_by',
    ];

    protected $casts = [
        'amount'       => 'double',
        'payment_date' => 'date',
    ];

    protected $appends = [
        'formatted_amount',
        'receipt_url',
        'created_by_title',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getFormattedAmountAttribute(): string
    {
        return '$ ' . number_format($this->amount ?? 0, 2);
    }

    public function getReceiptUrlAttribute(): ?string
    {
        if ($this->receipt_path) {
            return asset($this->receipt_path);
        }
        return null;
    }

    public function getCreatedByTitleAttribute(): ?string
    {
        return $this->createdBy?->name;
    }
}
