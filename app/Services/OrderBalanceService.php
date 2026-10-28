<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderBalanceService
{
    public const MAX_CENTS = 999999999999999; // 9,999,999,999,999.99

    public function toCents(float|int|string|null $amount): int
    {
        $cents = (int) round((float) ($amount ?? 0) * 100);
        if ($cents < 0) {
            return 0;
        }
        if ($cents > self::MAX_CENTS) {
            return self::MAX_CENTS;
        }
        return $cents;
    }

    public function fromCents(int $cents): float
    {
        return round($cents / 100, 2);
    }

    public function currentOrderTotal(Order $order): float
    {
        return (float) ($order->total_price ?? 0);
    }

    public function previousBalance(Order $order): float
    {
        return (float) ($order->previous_balance ?? 0);
    }

    public function totalAmountDue(Order $order): float
    {
        $totalCents = $this->toCents($order->total_price) + $this->toCents($order->previous_balance);
        return $this->fromCents($totalCents);
    }

    public function paidAmount(Order $order): float
    {
        return (float) ($order->paid_amount ?? 0);
    }

    public function transferredOutAmount(Order $order): float
    {
        return (float) ($order->transferred_out_amount ?? 0);
    }

    public function remainingAmount(Order $order): float
    {
        $dueCents = $this->toCents($order->total_price) + $this->toCents($order->previous_balance);
        $paidCents = $this->toCents($order->paid_amount);
        $transferredOutCents = $this->toCents($order->transferred_out_amount);

        $remainingCents = max(0, $dueCents - $paidCents - $transferredOutCents);
        return $this->fromCents($remainingCents);
    }

    public function activeOutstandingAmount(Order $order): float
    {
        if ($order->trashed() || $order->payment_status === 'Cancel') {
            return 0.0;
        }

        return $this->remainingAmount($order);
    }

    public function projection(Order $order): array
    {
        $current = $this->currentOrderTotal($order);
        $previous = $this->previousBalance($order);
        $totalDue = $this->totalAmountDue($order);
        $paid = $this->paidAmount($order);
        $transferredOut = $this->transferredOutAmount($order);
        $remaining = $this->remainingAmount($order);
        $activeOutstanding = $this->activeOutstandingAmount($order);

        return [
            'current_order_total' => $current,
            'current_order_total_formatted' => '$' . number_format($current, 2),
            'previous_balance' => $previous,
            'previous_balance_formatted' => '$' . number_format($previous, 2),
            'total_amount_due' => $totalDue,
            'total_amount_due_formatted' => '$' . number_format($totalDue, 2),
            'paid_amount' => $paid,
            'paid_amount_formatted' => '$' . number_format($paid, 2),
            'transferred_out_amount' => $transferredOut,
            'transferred_out_amount_formatted' => '$' . number_format($transferredOut, 2),
            'remaining_amount' => $remaining,
            'remaining_amount_formatted' => '$' . number_format($remaining, 2),
            'active_outstanding' => $activeOutstanding,
            'active_outstanding_formatted' => '$' . number_format($activeOutstanding, 2),
            'is_transferred' => $transferredOut > 0,
        ];
    }
}
