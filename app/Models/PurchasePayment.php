<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchasePayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'purchase_payments';

    protected $fillable = [
        'purchase_id',
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
        'payment_date' => 'date:Y-m-d',
        'amount'       => 'double',
    ];

    protected $appends = [
        'created_by_title',
        'formatted_amount',
        'formatted_date',
        'payment_method_title',
        'receipt_url',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->created_by) && auth()->check()) {
                $model->created_by = auth()->id();
            }

            if (empty($model->payment_number)) {
                $model->payment_number = static::generatePaymentNumber($model->payment_date ?? now());
            }

            if (empty($model->payment_method)) {
                $model->payment_method = 'cash';
            }
        });

        static::saved(function ($model) {
            if ($model->purchase) {
                $model->purchase->recalculateTotals();
            }
        });

        static::deleted(function ($model) {
            if ($model->purchase) {
                $model->purchase->recalculateTotals();
            }
        });
    }

    // Relationships
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    // Accessors
    public function getCreatedByTitleAttribute(): string
    {
        return $this->createdBy?->name ?? ($this->createdBy?->username ?? '---');
    }

    public function getFormattedAmountAttribute(): string
    {
        return '$' . number_format((float) $this->amount, 2);
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->payment_date ? Carbon::parse($this->payment_date)->format('d/M/Y') : '---';
    }

    public function getPaymentMethodTitleAttribute(): string
    {
        return match (strtolower($this->payment_method ?? '')) {
            'cash'          => __('stock_in.payment_method.cash') ?? 'Cash',
            'bank_transfer' => __('stock_in.payment_method.bank_transfer') ?? 'Bank Transfer',
            'aba'           => 'ABA Pay',
            'cheque'        => __('stock_in.payment_method.cheque') ?? 'Cheque',
            'other'         => __('stock_in.payment_method.other') ?? 'Other',
            default         => ucfirst(str_replace('_', ' ', $this->payment_method ?? 'Cash')),
        };
    }

    public function getReceiptUrlAttribute(): ?string
    {
        if ($this->receipt_path) {
            return url($this->receipt_path);
        }
        return null;
    }

    // Generator
    public static function generatePaymentNumber($date = null): string
    {
        $carbonDate = $date ? Carbon::parse($date) : Carbon::now();
        $prefix = 'PAY-PO-' . $carbonDate->format('Ym') . '-';

        $lastRecord = static::withTrashed()
            ->where('payment_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastRecord && preg_match('/(\d+)$/', $lastRecord->payment_number, $matches)) {
            $nextSeq = (int) $matches[1] + 1;
        } else {
            $nextSeq = 1;
        }

        return $prefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
    }
}
