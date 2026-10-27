<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OtherIncomePayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'other_income_payments';

    protected $fillable = [
        'other_income_id',
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
        'amount'       => 'decimal:2',
        'payment_date' => 'date:Y-m-d',
    ];

    protected $appends = [
        'formatted_amount',
        'receipt_url',
    ];

    public function otherIncome(): BelongsTo
    {
        return $this->belongsTo(OtherIncome::class, 'other_income_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getFormattedAmountAttribute(): string
    {
        return '$' . number_format((float) $this->amount, 2);
    }

    public function getReceiptUrlAttribute(): ?string
    {
        if ($this->receipt_path) {
            return asset($this->receipt_path);
        }
        return null;
    }
}
