<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'purchases';

    protected $fillable = [
        'purchase_number',
        'supplier_invoice_no',
        'supplier_id',
        'shop_id',
        'purchase_date',
        'delivery_date',
        'status',
        'payment_status',
        'payment_due_date',
        'subtotal',
        'discount_type',
        'discount_rate',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'shipping_cost',
        'total_amount',
        'paid_amount',
        'creation_request_key',
        'remark',
        'created_by',
        'received_by',
        'received_at',
    ];

    protected $casts = [
        'purchase_date'    => 'date',
        'delivery_date'    => 'date',
        'payment_due_date' => 'date',
        'received_at'      => 'datetime',
        'subtotal'         => 'double',
        'discount_rate'    => 'double',
        'discount_amount'  => 'double',
        'tax_rate'         => 'double',
        'tax_amount'       => 'double',
        'shipping_cost'    => 'double',
        'total_amount'     => 'double',
        'paid_amount'      => 'double',
    ];

    protected $appends = [
        'remaining_amount',
        'formatted_total',
        'formatted_paid',
        'formatted_remaining',
        'status_badge',
        'payment_status_badge',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class, 'purchase_id');
    }

    public function payments()
    {
        return $this->hasMany(PurchasePayment::class, 'purchase_id');
    }

    public function getRemainingAmountAttribute(): float
    {
        $total = (float) ($this->total_amount ?? 0);
        $paid = (float) ($this->paid_amount ?? 0);
        return max(0.00, round($total - $paid, 2));
    }

    public function getFormattedTotalAttribute(): string
    {
        return '$ ' . number_format($this->total_amount ?? 0, 2);
    }

    public function getFormattedPaidAttribute(): string
    {
        return '$ ' . number_format($this->paid_amount ?? 0, 2);
    }

    public function getFormattedRemainingAttribute(): string
    {
        return '$ ' . number_format($this->remaining_amount ?? 0, 2);
    }

    public function getStatusBadgeAttribute(): array
    {
        $map = [
            'draft' => [
                'label' => __('purchase.status.draft'),
                'class' => 'status-badge-draft',
            ],
            'ordered' => [
                'label' => __('purchase.status.ordered'),
                'class' => 'status-badge-ordered',
            ],
            'partial_received' => [
                'label' => __('purchase.status.partial_received'),
                'class' => 'status-badge-partial-received',
            ],
            'received' => [
                'label' => __('purchase.status.received'),
                'class' => 'status-badge-received',
            ],
            'cancelled' => [
                'label' => __('purchase.status.cancelled'),
                'class' => 'status-badge-cancelled',
            ],
        ];

        return $map[$this->status] ?? [
            'label' => ucfirst($this->status ?? 'Draft'),
            'class' => 'status-badge-default',
        ];
    }

    public function getPaymentStatusBadgeAttribute(): array
    {
        $map = [
            'unpaid' => [
                'label' => __('purchase.payment_status.unpaid'),
                'class' => 'payment-badge-unpaid',
            ],
            'partial' => [
                'label' => __('purchase.payment_status.partial'),
                'class' => 'payment-badge-partial',
            ],
            'paid' => [
                'label' => __('purchase.payment_status.paid'),
                'class' => 'payment-badge-paid',
            ],
        ];

        return $map[$this->payment_status] ?? [
            'label' => ucfirst($this->payment_status ?? 'Unpaid'),
            'class' => 'payment-badge-default',
        ];
    }
}
