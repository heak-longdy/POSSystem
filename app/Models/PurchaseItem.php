<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'purchase_items';

    protected $fillable = [
        'purchase_id',
        'product_id',
        'uom_id',
        'ordered_qty',
        'received_qty',
        'unit_cost',
        'discount_amount',
        'tax_amount',
        'subtotal',
        'update_product_cost',
        'remark',
    ];

    protected $casts = [
        'ordered_qty'         => 'decimal:2',
        'received_qty'        => 'decimal:2',
        'unit_cost'           => 'double',
        'discount_amount'     => 'double',
        'tax_amount'          => 'double',
        'subtotal'            => 'double',
        'update_product_cost' => 'boolean',
    ];

    protected $appends = [
        'product_title',
        'uom_title',
        'pending_qty',
        'formatted_unit_cost',
        'formatted_subtotal',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function uom()
    {
        return $this->belongsTo(UOM::class, 'uom_id');
    }

    public function getProductTitleAttribute(): ?string
    {
        return $this->product?->name;
    }

    public function getUomTitleAttribute(): ?string
    {
        return $this->uom?->name ?? $this->product?->uom?->name;
    }

    public function getPendingQtyAttribute(): float
    {
        $ordered = (float) ($this->ordered_qty ?? 0);
        $received = (float) ($this->received_qty ?? 0);
        return max(0.00, round($ordered - $received, 2));
    }

    public function getFormattedUnitCostAttribute(): string
    {
        return '$ ' . number_format($this->unit_cost ?? 0, 2);
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return '$ ' . number_format($this->subtotal ?? 0, 2);
    }
}
