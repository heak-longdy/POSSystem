<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'orders';

    protected $fillable = [
        'customer_id',
        'previous_balance',
        'total_price',
        'total_commission',
        'total_discount',
        'paid_amount',
        'shop_id',
        'order_date',
        'delivery_date',
        'payment_status',
        'payment_date',
        'invoice_number',
        'pay_way',
        'total_point',
        'remark',
        'creation_request_key',
        'creation_payload_hash',
        'created_by',
    ];

    protected $casts = [
        'previous_balance' => 'double',
        'total_price' => 'double',
        'total_discount' => 'double',
        'total_commission' => 'double',
        'paid_amount' => 'double',
        'order_date' => 'datetime',
        'delivery_date' => 'datetime',
        'payment_date' => 'datetime',
    ];

    protected $appends = ['remaining_amount', 'total_amount_due', 'transferred_out_amount', 'is_transferred'];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function barber()
    {
        return $this->belongsTo(Barber::class, 'barber_id');
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'order_id');
    }

    public function payments()
    {
        return $this->hasMany(OrderPayment::class, 'order_id')
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc');
    }

    public function incomingBalanceTransfers()
    {
        return $this->hasMany(OrderBalanceTransfer::class, 'new_order_id');
    }

    public function outgoingBalanceTransfers()
    {
        return $this->hasMany(OrderBalanceTransfer::class, 'previous_order_id');
    }

    public function completedOutgoingBalanceTransfer()
    {
        return $this->hasOne(OrderBalanceTransfer::class, 'previous_order_id')
            ->where('transfer_status', 'completed');
    }

    public function getTotalAmountDueAttribute(): float
    {
        return (float) ($this->total_price ?? 0) + (float) ($this->previous_balance ?? 0);
    }

    public function getTransferredOutAmountAttribute(): float
    {
        if ($this->relationLoaded('completedOutgoingBalanceTransfer')) {
            return (float) ($this->completedOutgoingBalanceTransfer?->transferred_amount ?? 0);
        }

        if ($this->relationLoaded('outgoingBalanceTransfers')) {
            return (float) ($this->outgoingBalanceTransfers->firstWhere('transfer_status', 'completed')?->transferred_amount ?? 0);
        }

        try {
            return (float) ($this->completedOutgoingBalanceTransfer()->value('transferred_amount') ?? 0);
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    public function getRemainingAmountAttribute(): float
    {
        $totalDue = (float) ($this->total_price ?? 0) + (float) ($this->previous_balance ?? 0);
        $paid = (float) ($this->paid_amount ?? 0);
        $transferredOut = $this->transferred_out_amount;

        return max(0, $totalDue - $paid - $transferredOut);
    }

    public function getIsTransferredAttribute(): bool
    {
        return $this->isTransferred();
    }

    public function isTransferred(): bool
    {
        return $this->transferred_out_amount > 0;
    }

    public function scopeForUser(Builder $query, ?User $user = null): Builder
    {
        $user = $user ?? auth()->user();

        if (!$user || $user->hasFullDataAccess()) {
            return $query;
        }

        return $query->where('orders.created_by', $user->id);
    }

    public function isOwnedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->hasFullDataAccess()) {
            return true;
        }

        return (int) $this->created_by === (int) $user->id;
    }
}
