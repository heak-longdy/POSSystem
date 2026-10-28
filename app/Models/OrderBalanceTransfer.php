<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderBalanceTransfer extends Model
{
    use HasFactory;

    protected $table = 'order_balance_transfers';

    protected $fillable = [
        'new_order_id',
        'previous_order_id',
        'transferred_amount',
        'original_payment_status',
        'transferred_at',
        'transferred_by',
        'transfer_status',
        'reversed_at',
        'reversed_by',
        'reversal_reason',
    ];

    protected $casts = [
        'transferred_amount' => 'double',
        'transferred_at' => 'datetime',
        'reversed_at' => 'datetime',
    ];

    public function newOrder()
    {
        return $this->belongsTo(Order::class, 'new_order_id');
    }

    public function destinationOrder()
    {
        return $this->newOrder();
    }

    public function previousOrder()
    {
        return $this->belongsTo(Order::class, 'previous_order_id');
    }

    public function sourceOrder()
    {
        return $this->previousOrder();
    }

    public function transferredBy()
    {
        return $this->belongsTo(User::class, 'transferred_by')->withTrashed();
    }

    public function reversedBy()
    {
        return $this->belongsTo(User::class, 'reversed_by')->withTrashed();
    }

    public function scopeCompleted($query)
    {
        return $query->where('transfer_status', 'completed');
    }

    public function scopeReversed($query)
    {
        return $query->where('transfer_status', 'reversed');
    }
}
