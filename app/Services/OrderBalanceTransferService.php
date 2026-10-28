<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderBalanceTransfer;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderBalanceTransferService
{
    protected OrderBalanceService $balanceService;

    public function __construct(OrderBalanceService $balanceService)
    {
        $this->balanceService = $balanceService;
    }

    /**
     * Get all orders eligible to have their outstanding balances transferred.
     */
    public function getEligiblePreviousOrders(int $customerId, ?int $excludeOrderId = null, bool $forUpdate = false): Collection
    {
        if ($customerId <= 0) {
            return collect([]);
        }

        $query = Order::query()
            ->where('customer_id', $customerId)
            ->whereIn('payment_status', ['Pending', 'Partial'])
            ->whereNull('deleted_at')
            ->whereDoesntHave('outgoingBalanceTransfers', function ($q) {
                $q->where('transfer_status', 'completed');
            })
            ->with(['payments', 'completedOutgoingBalanceTransfer'])
            ->orderBy('order_date', 'asc')
            ->orderBy('id', 'asc');

        if ($excludeOrderId) {
            $query->where('id', '!=', $excludeOrderId);
        }

        if ($forUpdate) {
            $query->lockForUpdate();
        }

        $candidates = $query->get();

        // Filter in PHP for positive active outstanding amount to guard against float rounding
        return $candidates->filter(function (Order $order) {
            return $this->balanceService->activeOutstandingAmount($order) > 0;
        })->values();
    }

    /**
     * Build preview data and fingerprint for the Create Order UI and validation.
     */
    public function previewPreviousBalance(int $customerId, ?int $excludeOrderId = null): array
    {
        if ($customerId <= 0) {
            return [
                'customer_id' => 0,
                'previous_balance' => 0.0,
                'previous_balance_formatted' => '$0.00',
                'previous_orders' => [],
                'previous_orders_count' => 0,
                'fingerprint' => '',
            ];
        }

        $eligibleOrders = $this->getEligiblePreviousOrders($customerId, $excludeOrderId);

        $totalCents = 0;
        $ordersData = [];
        $fingerprintItems = [];

        foreach ($eligibleOrders as $order) {
            $due = $this->balanceService->totalAmountDue($order);
            $paid = $this->balanceService->paidAmount($order);
            $remaining = $this->balanceService->activeOutstandingAmount($order);
            $remCents = $this->balanceService->toCents($remaining);

            $totalCents += $remCents;
            $fingerprintItems[] = "{$order->id}:{$remCents}";

            $ordersData[] = [
                'id' => $order->id,
                'invoice_number' => $order->invoice_number ?: '#' . $order->id,
                'order_date' => $order->order_date ? Carbon::parse($order->order_date)->format('Y-m-d H:i') : '---',
                'payment_status' => $order->payment_status,
                'own_sale' => (float) ($order->total_price ?? 0),
                'own_sale_formatted' => '$' . number_format((float) ($order->total_price ?? 0), 2),
                'previous_balance' => (float) ($order->previous_balance ?? 0),
                'previous_balance_formatted' => '$' . number_format((float) ($order->previous_balance ?? 0), 2),
                'total_amount_due' => $due,
                'total_amount_due_formatted' => '$' . number_format($due, 2),
                'paid_amount' => $paid,
                'paid_amount_formatted' => '$' . number_format($paid, 2),
                'remaining_amount' => $remaining,
                'remaining_amount_formatted' => '$' . number_format($remaining, 2),
            ];
        }

        $totalPreviousBalance = $this->balanceService->fromCents($totalCents);
        $fingerprint = hash('sha256', implode('|', $fingerprintItems));

        return [
            'customer_id' => $customerId,
            'previous_balance' => $totalPreviousBalance,
            'previous_balance_formatted' => '$' . number_format($totalPreviousBalance, 2),
            'previous_orders' => $ordersData,
            'previous_orders_count' => count($ordersData),
            'fingerprint' => $fingerprint,
        ];
    }

    /**
     * Atomically consolidate previous balances into the new order.
     * Must be called within an active DB transaction.
     */
    public function consolidateBalances(Order $newOrder, ?int $operatorId = null): float
    {
        if (!$newOrder->customer_id) {
            return 0.0;
        }

        // Lock eligible orders in deterministic order to prevent deadlocks
        $eligibleOrders = $this->getEligiblePreviousOrders(
            (int) $newOrder->customer_id,
            $newOrder->id,
            true // lockForUpdate
        );

        if ($eligibleOrders->isEmpty()) {
            return 0.0;
        }

        $operatorId = $operatorId ?: (Auth::id() ?: $newOrder->created_by);
        $now = Carbon::now();
        $totalTransferredCents = 0;

        foreach ($eligibleOrders as $sourceOrder) {
            // Guard: ensure destination order ID is strictly greater than source order ID (forward ID)
            if ($newOrder->id && $sourceOrder->id >= $newOrder->id) {
                continue;
            }

            $remCents = $this->balanceService->toCents(
                $this->balanceService->activeOutstandingAmount($sourceOrder)
            );

            if ($remCents <= 0) {
                continue;
            }

            $transferredAmount = $this->balanceService->fromCents($remCents);
            $origStatus = $sourceOrder->payment_status;

            OrderBalanceTransfer::create([
                'new_order_id' => $newOrder->id,
                'previous_order_id' => $sourceOrder->id,
                'transferred_amount' => $transferredAmount,
                'original_payment_status' => $origStatus,
                'transferred_at' => $now,
                'transferred_by' => $operatorId,
                'transfer_status' => 'completed',
            ]);

            // Update source order to Paid via transfer
            $sourceOrder->update([
                'payment_status' => 'Paid',
                'payment_date' => $sourceOrder->payment_date ?: $now,
            ]);

            $totalTransferredCents += $remCents;
        }

        $transferredTotal = $this->balanceService->fromCents($totalTransferredCents);
        if ($transferredTotal > 0 && (float) ($newOrder->previous_balance ?? 0) <= 0) {
            $newOrder->update(['previous_balance' => $transferredTotal]);
            $newOrder->previous_balance = $transferredTotal;
        }

        return $transferredTotal;
    }

    /**
     * Safely reverse incoming transfers for a cancelled order.
     * Must be called within an active DB transaction.
     */
    public function reverseTransfersForOrder(Order $order, string $reason, ?int $operatorId = null): void
    {
        $incomingTransfers = OrderBalanceTransfer::where('new_order_id', $order->id)
            ->where('transfer_status', 'completed')
            ->lockForUpdate()
            ->get();

        if ($incomingTransfers->isEmpty()) {
            return;
        }

        $operatorId = $operatorId ?: Auth::id();
        $now = Carbon::now();

        foreach ($incomingTransfers as $transfer) {
            $sourceOrder = Order::where('id', $transfer->previous_order_id)
                ->lockForUpdate()
                ->first();

            if ($sourceOrder) {
                // Restore original payment status
                $restoreStatus = in_array($transfer->original_payment_status, ['Pending', 'Partial'])
                    ? $transfer->original_payment_status
                    : 'Pending';

                $sourceOrder->update([
                    'payment_status' => $restoreStatus,
                    'payment_date' => $restoreStatus === 'Pending' && (float) ($sourceOrder->paid_amount ?? 0) <= 0
                        ? null
                        : $sourceOrder->payment_date,
                ]);
            }

            $transfer->update([
                'transfer_status' => 'reversed',
                'reversed_at' => $now,
                'reversed_by' => $operatorId,
                'reversal_reason' => $reason,
            ]);
        }
    }
}
