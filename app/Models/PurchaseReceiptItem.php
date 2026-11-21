<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseReceiptItem extends Model
{
    use HasFactory;

    protected $table = 'purchase_receipt_items';

    protected $fillable = [
        'purchase_receipt_id',
        'purchase_item_id',
        'product_id',
        'qty_received',
        'stock_history_id',
    ];

    protected $casts = [
        'qty_received' => 'double',
    ];

    protected $appends = [
        'product_name',
        'product_image',
    ];

    // Relationships
    public function receipt(): BelongsTo
    {
        return $this->belongsTo(PurchaseReceipt::class, 'purchase_receipt_id');
    }

    public function purchaseItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseItem::class, 'purchase_item_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function stockHistory(): BelongsTo
    {
        return $this->belongsTo(StockHistory::class, 'stock_history_id');
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
}
