<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OtherIncome extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'other_incomes';

    protected $fillable = [
        'income_number',
        'category_id',
        'supplier_id',
        'shop_id',
        'title',
        'income_date',
        'received_amount',
        'payment_status',
        'reference_no',
        'attachment_path',
        'attachment_name',
        'description',
        'created_by',
        'status',
    ];

    protected $casts = [
        'income_date'     => 'date:Y-m-d',
        'received_amount' => 'decimal:2',
        'status'          => 'integer',
    ];

    protected $appends = [
        'supplier_name',
        'category_name',
        'shop_name',
        'formatted_date',
        'formatted_amount',
        'formatted_received_amount',
        'payment_status_badge',
        'attachment_url',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->income_number)) {
                $datePrefix = date('ym');
                $lastRecord = static::withTrashed()
                    ->where('income_number', 'like', "INC-{$datePrefix}-%")
                    ->orderBy('id', 'desc')
                    ->first();

                $sequence = 1;
                if ($lastRecord && preg_match('/INC-\d{4}-(\d+)/', $lastRecord->income_number, $matches)) {
                    $sequence = ((int) $matches[1]) + 1;
                }

                $model->income_number = sprintf('INC-%s-%04d', $datePrefix, $sequence);
            }

            if (empty($model->created_by)) {
                $model->created_by = auth()->check() ? auth()->id() : 1;
            }

            if (empty($model->payment_status)) {
                $model->payment_status = 'paid';
            }
        });

        static::updating(function ($model) {
            if (empty($model->payment_status)) {
                $model->payment_status = 'paid';
            }
        });
    }

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(OtherIncomeCategory::class, 'category_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(OtherIncomePayment::class, 'other_income_id')->orderBy('payment_date', 'asc');
    }

    // Query Scopes
    public function scopeDateBetween(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from && $to) {
            return $query->whereBetween('income_date', [$from, $to]);
        } elseif ($from) {
            return $query->whereDate('income_date', '>=', $from);
        } elseif ($to) {
            return $query->whereDate('income_date', '<=', $to);
        }
        return $query;
    }

    public function scopeOfSupplier(Builder $query, $supplierId): Builder
    {
        return $supplierId ? $query->where('supplier_id', $supplierId) : $query;
    }

    public function scopeOfCategory(Builder $query, $categoryId): Builder
    {
        return $categoryId ? $query->where('category_id', $categoryId) : $query;
    }

    public function scopeOfShop(Builder $query, $shopId): Builder
    {
        return $shopId ? $query->where('shop_id', $shopId) : $query;
    }

    public function scopeOfPaymentStatus(Builder $query, ?string $status): Builder
    {
        return ($status && $status !== 'all') ? $query->where('payment_status', $status) : $query;
    }

    // Accessors
    public function getSupplierNameAttribute(): string
    {
        return $this->supplier ? $this->supplier->name : '---';
    }

    public function getCategoryNameAttribute(): string
    {
        return $this->category ? $this->category->name : '---';
    }

    public function getShopNameAttribute(): string
    {
        return $this->shop ? $this->shop->name : __('other_income.all_shops');
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->income_date ? Carbon::parse($this->income_date)->format('Y-m-d') : '---';
    }

    public function getFormattedExpectedAmountAttribute(): string
    {
        return '$' . number_format((float) $this->expected_amount, 2);
    }

    public function getFormattedAmountAttribute(): string
    {
        return '$' . number_format((float) ($this->received_amount ?? 0), 2);
    }

    public function getFormattedReceivedAmountAttribute(): string
    {
        return $this->getFormattedAmountAttribute();
    }

    public function getFormattedRemainingAmountAttribute(): string
    {
        return '$' . number_format((float) $this->remaining_amount, 2);
    }

    public function getPaymentStatusBadgeAttribute(): string
    {
        return match ($this->payment_status) {
            'paid'      => '<span class="badge bg-success">' . __('other_income.payment_status.paid') . '</span>',
            'partial'   => '<span class="badge bg-warning text-dark">' . __('other_income.payment_status.partial') . '</span>',
            'cancelled' => '<span class="badge bg-danger">' . __('other_income.payment_status.cancelled') . '</span>',
            default     => '<span class="badge bg-secondary">' . __('other_income.payment_status.pending') . '</span>',
        };
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        if ($this->attachment_path) {
            return asset($this->attachment_path);
        }
        return null;
    }
}
