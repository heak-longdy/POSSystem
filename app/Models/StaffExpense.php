<?php

namespace App\Models;

use App\Enums\ExpenseType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffExpense extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'staff_expenses';

    protected $fillable = [
        'staff_id',
        'shop_id',
        'type',
        'amount',
        'expense_date',
        'description',
        'created_by',
        'status',
        'user',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if (empty($model->created_by)) {
                $model->created_by = $model->user ?? (auth()->check() ? auth()->id() : 1);
            }
            unset($model->attributes['user']);
        });
    }

    public function setShopIdAttribute($value)
    {
        $this->attributes['shop_id'] = $value ?: null;
    }

    public function setUserAttribute($value)
    {
        $this->attributes['created_by'] = $value ?: (auth()->check() ? auth()->id() : 1);
    }

    public function getUserAttribute()
    {
        return $this->created_by;
    }

    public function setCreatedByAttribute($value)
    {
        $this->attributes['created_by'] = $value ?: (auth()->check() ? auth()->id() : 1);
    }

    protected $casts = [
        'amount'       => 'decimal:2',
        'expense_date' => 'date:Y-m-d',
        'type'         => ExpenseType::class,
    ];

    // Relationships
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Query Scopes
    public function scopeDateBetween(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from && $to) {
            return $query->whereBetween('expense_date', [$from, $to]);
        }
        return $query;
    }

    public function scopeOfStaff(Builder $query, ?int $staffId): Builder
    {
        return $staffId ? $query->where('staff_id', $staffId) : $query;
    }

    public function scopeOfType(Builder $query, ?string $type): Builder
    {
        return $type ? $query->where('type', $type) : $query;
    }

    public function scopeOfShop(Builder $query, ?int $shopId): Builder
    {
        return $shopId ? $query->where('shop_id', $shopId) : $query;
    }

    public function getStaffNameAttribute()
    {
        return $this->staff ? $this->staff->name : '---';
    }

    public function getShopNameAttribute()
    {
        return $this->shop ? $this->shop->name : 'All Shops';
    }

    public function getTypeBadgeAttribute()
    {
        $badgeClass = $this->type ? $this->type->badgeClass() : 'badge bg-secondary';
        $label = $this->type ? $this->type->label() : '--';
        return '<span class="' . $badgeClass . '">' . $label . '</span>';
    }

    public function getFormattedAmountAttribute()
    {
        $isDeduction = $this->type && $this->type->isDeduction();
        $class = $isDeduction ? 'text-danger' : 'text-success';
        $prefix = $isDeduction ? '-' : '+';
        return '<span class="font-weight-bold ' . $class . '">' . $prefix . '$' . number_format($this->amount, 2) . '</span>';
    }

    public function getFormattedDateAttribute()
    {
        return $this->expense_date ? (is_object($this->expense_date) ? $this->expense_date->format('Y-m-d') : $this->expense_date) : '--';
    }

    public function getCreatorNameAttribute()
    {
        return $this->createdBy ? $this->createdBy->name : '---';
    }
}
