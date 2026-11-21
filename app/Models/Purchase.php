<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'purchases';

    protected $fillable = [
        'purchase_number',
        'supplier_id',
        'shop_id',
        'order_date',
        'expected_delivery_date',
        'order_status',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'shipping_cost',
        'total_amount',
        'paid_amount',
        'payment_status',
        'payment_method',
        'reference_no',
        'remark',
        'created_by',
        'status',
    ];

    protected $casts = [
        'order_date'             => 'date:Y-m-d',
        'expected_delivery_date' => 'date:Y-m-d',
        'subtotal'               => 'double',
        'discount_amount'        => 'double',
        'tax_amount'             => 'double',
        'shipping_cost'          => 'double',
        'total_amount'           => 'double',
        'paid_amount'            => 'double',
        'status'                 => 'integer',
    ];

    protected $appends = [
        'remaining_amount',
        'supplier_title',
        'shop_title',
        'created_by_title',
        'created_date',
        'order_status_title',
        'payment_status_title',
        'formatted_total_amount',
        'formatted_paid_amount',
        'formatted_remaining_amount',
        'formatted_remaining_amount_styled',
        'purchase_number_link',
        'order_status_badge',
        'payment_status_badge',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->created_by) && auth()->check()) {
                $model->created_by = auth()->id();
            }

            if (empty($model->purchase_number)) {
                $model->purchase_number = static::generatePurchaseNumber($model->order_date ?? now());
            }

            if (empty($model->order_status)) {
                $model->order_status = 'ordered';
            }

            if (empty($model->payment_status)) {
                $model->payment_status = 'unpaid';
            }
        });
    }

    // Relationships
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
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class, 'purchase_id');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(PurchaseReceipt::class, 'purchase_id')->orderBy('received_date', 'desc');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PurchasePayment::class, 'purchase_id')->orderBy('payment_date', 'asc');
    }

    public function stockIns(): HasMany
    {
        return $this->hasMany(StockIn::class, 'purchase_id');
    }

    // Accessors
    public function getRemainingAmountAttribute(): float
    {
        $remaining = (float) $this->total_amount - (float) $this->paid_amount;
        return max(0.00, round($remaining, 2));
    }

    public function getSupplierTitleAttribute(): ?string
    {
        return $this->supplier?->name ?? '---';
    }

    public function getShopTitleAttribute(): ?string
    {
        return $this->shop?->name ?? '---';
    }

    public function getCreatedByTitleAttribute(): ?string
    {
        return $this->createdBy?->name ?? ($this->createdBy?->username ?? '---');
    }

    public function getCreatedDateAttribute(): ?string
    {
        return $this->order_date ? Carbon::parse($this->order_date)->format('d/M/Y') : ($this->created_at ? Carbon::parse($this->created_at)->format('d/M/Y') : '---');
    }

    public function getOrderStatusTitleAttribute(): string
    {
        return match ($this->order_status) {
            'draft'            => __('stock_in.status.draft') ?? 'Draft',
            'ordered'          => __('stock_in.status.ordered') ?? 'Ordered',
            'partial_received' => __('stock_in.status.partial_received') ?? 'Partial Received',
            'received'         => __('stock_in.status.received') ?? 'Received',
            'cancelled'        => __('stock_in.status.cancelled') ?? 'Cancelled',
            default            => ucfirst(str_replace('_', ' ', $this->order_status ?? 'ordered')),
        };
    }

    public function getPaymentStatusTitleAttribute(): string
    {
        return match ($this->payment_status) {
            'unpaid'  => __('stock_in.payment_status.unpaid') ?? 'Unpaid',
            'partial' => __('stock_in.payment_status.partial') ?? 'Partial',
            'paid'    => __('stock_in.payment_status.paid') ?? 'Paid',
            default   => ucfirst($this->payment_status ?? 'unpaid'),
        };
    }

    public function getFormattedTotalAmountAttribute(): string
    {
        return '$' . number_format((float) $this->total_amount, 2);
    }

    public function getFormattedPaidAmountAttribute(): string
    {
        return '$' . number_format((float) $this->paid_amount, 2);
    }

    public function getFormattedRemainingAmountAttribute(): string
    {
        return '$' . number_format($this->remaining_amount, 2);
    }

    public function getFormattedRemainingAmountStyledAttribute(): string
    {
        $colorClass = $this->remaining_amount > 0 ? 'text-rose-600 font-semibold' : 'text-slate-500 font-semibold';
        return '<span class="' . $colorClass . '">' . $this->formatted_remaining_amount . '</span>';
    }

    public function getPurchaseNumberLinkAttribute(): string
    {
        $url = route('admin-stock-in-view', $this->id);
        $html = '<a href="' . $url . '" class="po-number-link"><strong>' . e($this->purchase_number) . '</strong></a>';
        if ($this->reference_no) {
            $html .= '<div style="font-size: 11px; color: #94a3b8; margin-top: 2px;">Ref: ' . e($this->reference_no) . '</div>';
        }
        return $html;
    }

    public function getOrderStatusBadgeAttribute(): string
    {
        $statusClass = match ($this->order_status) {
            'draft'            => 'badge-draft',
            'ordered'          => 'badge-ordered',
            'partial_received' => 'badge-partial',
            'received'         => 'badge-received',
            'cancelled'        => 'badge-cancelled',
            default            => 'badge-default',
        };
        return '<span class="status-badge ' . $statusClass . '">' . e($this->order_status_title) . '</span>';
    }

    public function getPaymentStatusBadgeAttribute(): string
    {
        $payClass = match ($this->payment_status) {
            'unpaid'  => 'badge-unpaid',
            'partial' => 'badge-partial',
            'paid'    => 'badge-paid',
            default   => 'badge-default',
        };
        return '<span class="status-badge ' . $payClass . '">' . e($this->payment_status_title) . '</span>';
    }

    // Number Generator
    public static function generatePurchaseNumber($date = null): string
    {
        $carbonDate = $date ? Carbon::parse($date) : Carbon::now();
        $prefix = 'PO-' . $carbonDate->format('Ym') . '-';

        $lastRecord = static::withTrashed()
            ->where('purchase_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastRecord && preg_match('/(\d+)$/', $lastRecord->purchase_number, $matches)) {
            $nextSeq = (int) $matches[1] + 1;
        } else {
            $nextSeq = 1;
        }

        return $prefix . str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);
    }

    // Recalculate status and balance
    public function recalculateTotals(): self
    {
        $this->loadMissing(['items', 'payments']);

        $subtotal = 0.00;
        $totalQty = 0.00;
        $totalReceived = 0.00;

        foreach ($this->items as $item) {
            $subtotal += (float) ($item->qty * $item->unit_cost);
            $totalQty += (float) $item->qty;
            $totalReceived += (float) $item->qty_received;
        }

        $this->subtotal = round($subtotal, 2);
        $total = $this->subtotal - (float) $this->discount_amount + (float) $this->tax_amount + (float) $this->shipping_cost;
        $this->total_amount = max(0.00, round($total, 2));

        $totalPaid = (float) $this->payments()->sum('amount');
        $this->paid_amount = round($totalPaid, 2);

        // Update payment status
        if ($this->paid_amount <= 0.00) {
            $this->payment_status = 'unpaid';
        } elseif ($this->paid_amount >= $this->total_amount && $this->total_amount > 0) {
            $this->payment_status = 'paid';
        } else {
            $this->payment_status = 'partial';
        }

        // Update order status if not draft/cancelled
        if (!in_array($this->order_status, ['draft', 'cancelled'])) {
            if ($totalReceived <= 0.00) {
                $this->order_status = 'ordered';
            } elseif ($totalReceived >= $totalQty && $totalQty > 0) {
                $this->order_status = 'received';
            } else {
                $this->order_status = 'partial_received';
            }
        }

        $this->save();
        return $this;
    }
}
