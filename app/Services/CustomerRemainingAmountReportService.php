<?php

namespace App\Services;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CustomerRemainingAmountReportService
{
    public function outstandingOrders(?int $shopId = null): Builder
    {
        return Order::query()
            ->where('payment_status', '!=', 'Cancel')
            // Use the current balance before date filtering or building history.
            // A settled invoice must not reappear when viewing an earlier period.
            ->whereRaw('ROUND(COALESCE(total_price, 0), 2) > ROUND(COALESCE(paid_amount, 0), 2)')
            ->when($shopId, fn ($query) => $query->where('shop_id', $shopId));
    }

    public function customers(array $filters)
    {
        $remaining = 'CASE WHEN ROUND(COALESCE(total_price, 0), 2) > ROUND(COALESCE(paid_amount, 0), 2) '
            . 'THEN ROUND(COALESCE(total_price, 0), 2) - ROUND(COALESCE(paid_amount, 0), 2) ELSE 0 END';

        $totals = $this->outstandingOrders($filters['shop_id'] ?? null)
            ->select('customer_id')
            ->selectRaw('COUNT(*) AS order_count')
            ->selectRaw('COUNT(*) AS outstanding_orders')
            ->selectRaw('SUM(ROUND(COALESCE(total_price, 0), 2)) AS total_amount')
            ->selectRaw('SUM(ROUND(COALESCE(paid_amount, 0), 2)) AS paid_amount')
            ->selectRaw("ROUND(SUM($remaining), 2) AS remaining_amount")
            ->selectRaw('MAX(COALESCE(order_date, created_at)) AS last_order_date')
            ->groupBy('customer_id');

        // Retain archived customers so their outstanding orders remain discoverable.
        return DB::table('customers')
            ->joinSub($totals, 'balances', 'balances.customer_id', '=', 'customers.id')
            ->select('customers.id', 'customers.name', 'customers.phone', 'customers.deleted_at', 'balances.*')
            ->where('balances.remaining_amount', '>', 0)
            ->when(!empty($filters['customer_id']), function ($query) use ($filters) {
                $query->where('customers.id', $filters['customer_id']);
            })
            ->when(isset($filters['search']) && $filters['search'] !== '', function ($query) use ($filters) {
                $search = $filters['search'];
                $query->where(function ($query) use ($search) {
                    $query->where('customers.name', 'like', '%' . $search . '%')
                        ->orWhere('customers.phone', 'like', '%' . $search . '%');
                });
            });
    }

    /**
     * Reconstruct the statement from saved orders and payment records. Calculate
     * in cents, before applying date filters or pagination, to preserve balances.
     */
    public function history(Collection $orders, ?string $from = null, ?string $to = null): array
    {
        $events = collect();
        $invoices = [];
        foreach ($orders as $order) {
            $orderDate = $order->order_date ?? $order->created_at;
            if ($order->payment_status) {
                $paymentStatus = $order->payment_status;
            } elseif ((float) $order->paid_amount <= 0) {
                $paymentStatus = 'Pending';
            } elseif ((float) $order->paid_amount >= (float) $order->total_price && (float) $order->total_price > 0) {
                $paymentStatus = 'Paid';
            } else {
                $paymentStatus = 'Partial';
            }

            $base = [
                'order_id' => $order->id,
                'invoice' => $order->invoice_number ?: '#' . $order->id,
                'shop' => $order->shop?->name,
                'payment_status' => $paymentStatus,
                'method' => null,
                'created_by' => null,
                'note' => null,
            ];
            $invoices[$order->id] = [
                'order_id' => $order->id,
                'invoice' => $base['invoice'],
                'shop' => $base['shop'],
                'payment_status' => $paymentStatus,
                'total_amount' => $this->cents($order->total_price),
                'paid_amount' => $this->cents($order->paid_amount),
                'current' => max(0, $this->cents($order->total_price) - $this->cents($order->paid_amount)),
                'opening' => 0,
                'closing' => 0,
                'rows' => collect(),
            ];
            $events->push(array_merge($base, [
                'date' => $orderDate,
                'sequence' => 0,
                'record_id' => $order->id,
                'type' => 'order',
                'amount' => $this->cents($order->total_price),
                'note' => $order->remark,
                'status' => $paymentStatus,
                'payment_status' => $paymentStatus,
            ]));

            $recordedPaid = 0;
            foreach ($order->payments as $payment) {
                $amount = $this->cents($payment->amount);
                $recordedPaid += $amount;
                $events->push(array_merge($base, [
                    'date' => $payment->payment_date ?? $payment->created_at ?? $orderDate,
                    'sequence' => 1,
                    'record_id' => $payment->id,
                    'type' => 'payment',
                    'amount' => -$amount,
                    'method' => $payment->payment_method,
                    'created_by' => $payment->createdBy?->name,
                    'note' => $payment->note,
                    'status' => 'Paid',
                    'payment_status' => 'Paid',
                ]));
            }

            // Legacy orders may have a paid total without itemized payments.
            // Label the difference explicitly; never invent an itemized receipt.
            $difference = $this->cents($order->paid_amount) - $recordedPaid;
            if ($difference !== 0) {
                $legacy = $order->payments->isEmpty();
                $events->push(array_merge($base, [
                    'date' => $legacy ? ($order->payment_date ?? $orderDate) : ($order->updated_at ?? $orderDate),
                    'sequence' => 2,
                    'record_id' => $order->id,
                    'type' => $legacy ? 'legacy_payment' : 'reconciliation',
                    'amount' => -$difference,
                    'note' => __('customer_remaining_report.' . ($legacy ? 'legacy_note' : 'reconciliation_note')),
                    'status' => 'Paid',
                    'payment_status' => 'Paid',
                ]));
            }
        }

        $events = $events->sort(function ($left, $right) {
            return [Carbon::parse($left['date'])->format('Y-m-d H:i:s'), $left['sequence'], $left['order_id'], $left['record_id']]
                <=> [Carbon::parse($right['date'])->format('Y-m-d H:i:s'), $right['sequence'], $right['order_id'], $right['record_id']];
        })->values();

        $orderBalances = [];
        $balance = $opening = $closing = $increases = $decreases = 0;
        $rows = collect();
        foreach ($events as $event) {
            $previous = $orderBalances[$event['order_id']] ?? 0;
            $orderBalances[$event['order_id']] = $previous + $event['amount'];
            // Match Order::remaining_amount: excess payment on one invoice does
            // not offset another invoice's outstanding balance.
            $change = max(0, $previous + $event['amount']) - max(0, $previous);
            $balance += $change;
            $event['date'] = Carbon::parse($event['date']);
            $event['change'] = $change;
            $event['balance'] = $balance;
            $event['invoice_balance'] = max(0, $orderBalances[$event['order_id']]);
            $day = $event['date']->format('Y-m-d');
            if ($from && $day < $from) {
                $opening = $balance;
                $invoices[$event['order_id']]['opening'] = $event['invoice_balance'];
            }
            if (!$to || $day <= $to) {
                $closing = $balance;
                $invoices[$event['order_id']]['closing'] = $event['invoice_balance'];
            }
            if ((!$from || $day >= $from) && (!$to || $day <= $to)) {
                $rows->push($event);
                $invoices[$event['order_id']]['rows']->push($event);
                $increases += max(0, $change);
                $decreases += max(0, -$change);
            }
        }

        return [
            'rows' => $rows,
            // Keep invoice histories intact and use natural invoice ordering
            // (INV-2 before INV-10), with dates ascending inside each invoice.
            'invoices' => collect($invoices)
                ->filter(fn ($invoice) => $invoice['rows']->isNotEmpty())
                ->sort(fn ($left, $right) => strnatcasecmp($left['invoice'], $right['invoice'])
                    ?: ($left['order_id'] <=> $right['order_id']))
                ->values(),
            'opening' => $opening,
            'closing' => $closing,
            'increases' => $increases,
            'decreases' => $decreases,
            'current' => $balance,
        ];
    }

    private function cents($amount): int
    {
        return (int) round((float) $amount * 100);
    }
}
