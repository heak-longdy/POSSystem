<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'purchase_items';

    protected $fillable = [
        'purchase_id',
        'product_id',
        'unit_cost',
        'qty',
        'qty_received',
        'subtotal',
        'remark',
    ];

    protected $casts = [
        'unit_cost'    => 'double',
        'qty'          => 'double',
        'qty_received' => 'double',
        'subtotal'     => 'double',
    ];

    protected $appends = [
        'product_name',
        'product_image',
        'category_title',
        'uom_title',
        'remaining_qty',
        'is_fully_received',
        'formatted_unit_cost',
        'formatted_subtotal',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->subtotal = round((float) $model->qty * (float) $model->unit_cost, 2);
        });
    }

    // Relationships
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function receiptItems(): HasMany
    {
        return $this->hasMany(PurchaseReceiptItem::class, 'purchase_item_id');
    }

    // Accessors
    public function getProductNameAttribute(): string
    {
        return $this->product?->name ?? '---';
    }

    public function getProductImageAttribute(): string
    {
        return $this->product?->image_url ?? asset('images/logo/default.png');
    }

    public function getCategoryTitleAttribute(): ?string
    {
        return $this->product?->category?->name ?? '---';
    }

    public function getUomTitleAttribute(): ?string
    {
        return $this->product?->uom?->name ?? '---';
    }

    public function getRemainingQtyAttribute(): float
    {
        $rem = (float) $this->qty - (float) $this->qty_received;
        return max(0.00, round($rem, 2));
    }

    public function getIsFullyReceivedAttribute(): bool
    {
        return (float) $this->qty_received >= (float) $this->qty && (float) $this->qty > 0;
    }

    public function getFormattedUnitCostAttribute(): string
    {
        return '$' . number_format((float) $this->unit_cost, 2);
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return '$' . number_format((float) $this->subtotal, 2);
    }
}
