<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseReceipt extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'purchase_receipts';

    protected $fillable = [
        'purchase_id',
        'receipt_number',
        'received_date',
        'received_by',
        'reference_no',
        'remark',
    ];

    protected $casts = [
        'received_date' => 'date:Y-m-d',
    ];

    protected $appends = [
        'received_by_title',
        'formatted_date',
        'total_qty_received',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->received_by) && auth()->check()) {
                $model->received_by = auth()->id();
            }

            if (empty($model->receipt_number)) {
                $model->receipt_number = static::generateReceiptNumber($model->received_date ?? now());
            }
        });
    }

    // Relationships
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by')->withTrashed();
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseReceiptItem::class, 'purchase_receipt_id');
    }

    // Accessors
    public function getReceivedByTitleAttribute(): string
    {
        return $this->receivedBy?->name ?? ($this->receivedBy?->username ?? '---');
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->received_date ? Carbon::parse($this->received_date)->format('d/M/Y') : '---';
    }

    public function getTotalQtyReceivedAttribute(): float
    {
        return (float) $this->items()->sum('qty_received');
    }

    // Generator
    public static function generateReceiptNumber($date = null): string
    {
        $carbonDate = $date ? Carbon::parse($date) : Carbon::now();
        $prefix = 'REC-' . $carbonDate->format('Ym') . '-';

        $lastRecord = static::withTrashed()
            ->where('receipt_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastRecord && preg_match('/(\d+)$/', $lastRecord->receipt_number, $matches)) {
            $nextSeq = (int) $matches[1] + 1;
        } else {
            $nextSeq = 1;
        }

        return $prefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
    }
}
