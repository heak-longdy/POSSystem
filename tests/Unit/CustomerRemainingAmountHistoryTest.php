<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\OrderPayment;
use App\Services\CustomerRemainingAmountReportService;
use Tests\TestCase;

class CustomerRemainingAmountHistoryTest extends TestCase
{
    private function order(int $id, float $total, float $paid, string $date, array $payments = []): Order
    {
        $order = new Order(['total_price' => $total, 'paid_amount' => $paid, 'order_date' => $date]);
        $order->id = $id;
        $order->setRelation('shop', null);
        $order->setRelation('payments', collect($payments)->map(function ($data) {
            $payment = new OrderPayment($data);
            $payment->id = $data['id'];
            $payment->setRelation('createdBy', null);
            return $payment;
        }));
        return $order;
    }

    public function test_date_range_preserves_opening_and_closing_balances_across_orders(): void
    {
        $orders = collect([
            $this->order(2, 40, 10, '2026-02-02 10:00:00', [
                ['id' => 3, 'amount' => 10, 'payment_date' => '2026-03-01 12:00:00'],
            ]),
            $this->order(1, 100, 50, '2026-01-01 10:00:00', [
                ['id' => 2, 'amount' => 20, 'payment_date' => '2026-02-03 23:59:59'],
                ['id' => 1, 'amount' => 30, 'payment_date' => '2026-01-02 12:00:00'],
            ]),
        ]);
        $history = app(CustomerRemainingAmountReportService::class)->history($orders, '2026-02-01', '2026-02-03');
        $this->assertSame(7000, $history['opening']);
        $this->assertSame(9000, $history['closing']);
        $this->assertSame(8000, $history['current']);
        $this->assertSame(4000, $history['increases']);
        $this->assertSame(2000, $history['decreases']);
        $this->assertSame([11000, 9000], $history['rows']->pluck('balance')->all());
    }

    public function test_empty_period_keeps_the_carried_balance(): void
    {
        $orders = collect([$this->order(1, 100, 0, '2026-01-01')]);
        $history = app(CustomerRemainingAmountReportService::class)->history($orders, '2026-02-01', '2026-02-28');
        $this->assertCount(0, $history['rows']);
        $this->assertSame(10000, $history['opening']);
        $this->assertSame(10000, $history['closing']);
    }

    public function test_legacy_paid_totals_are_labeled_and_reconcile_with_the_order(): void
    {
        $orders = collect([$this->order(1, 120, 35, '2026-01-01')]);
        $history = app(CustomerRemainingAmountReportService::class)->history($orders);
        $this->assertSame(['order', 'legacy_payment'], $history['rows']->pluck('type')->all());
        $this->assertSame(8500, $history['closing']);
    }

    public function test_cents_and_invoice_overpayments_do_not_erase_other_debts(): void
    {
        $orders = collect([
            $this->order(1, 0.30, 0.30, '2026-01-01', [
                ['id' => 1, 'amount' => 0.10, 'payment_date' => '2026-01-02'],
                ['id' => 2, 'amount' => 0.20, 'payment_date' => '2026-01-03'],
            ]),
            $this->order(2, 100, 110, '2026-01-01'),
            $this->order(3, 50, 0, '2026-01-01'),
        ]);
        $history = app(CustomerRemainingAmountReportService::class)->history($orders);
        $this->assertSame(5000, $history['current']);
        $this->assertCount(6, $history['rows']);
    }

    public function test_saved_total_discrepancies_are_explicit_reconciliations(): void
    {
        $order = $this->order(1, 100, 25, '2026-01-01', [
            ['id' => 1, 'amount' => 30, 'payment_date' => '2026-01-02'],
        ]);
        $order->updated_at = '2026-01-03';
        $history = app(CustomerRemainingAmountReportService::class)->history(collect([$order]));
        $this->assertSame('reconciliation', $history['rows']->last()['type']);
        $this->assertSame(500, $history['rows']->last()['change']);
        $this->assertSame(7500, $history['current']);
    }

    public function test_invoice_groups_use_natural_sort_and_independent_chronological_balances(): void
    {
        $invoiceTen = $this->order(1, 100, 30, '2026-01-01', [
            ['id' => 2, 'amount' => 10, 'payment_date' => '2026-01-04'],
            ['id' => 1, 'amount' => 20, 'payment_date' => '2026-01-02'],
        ]);
        $invoiceTen->invoice_number = 'INV-10';
        $invoiceTwo = $this->order(2, 50, 10, '2026-01-01', [
            ['id' => 3, 'amount' => 10, 'payment_date' => '2026-01-03'],
        ]);
        $invoiceTwo->invoice_number = 'INV-2';
        $history = app(CustomerRemainingAmountReportService::class)->history(collect([$invoiceTen, $invoiceTwo]));
        $this->assertSame(['INV-2', 'INV-10'], $history['invoices']->pluck('invoice')->all());
        $this->assertSame([5000, 4000], $history['invoices'][0]['rows']->pluck('invoice_balance')->all());
        $this->assertSame([10000, 8000, 7000], $history['invoices'][1]['rows']->pluck('invoice_balance')->all());

        $filtered = app(CustomerRemainingAmountReportService::class)->history(collect([$invoiceTen, $invoiceTwo]), '2026-01-02', '2026-01-03');
        $this->assertSame([5000, 10000], $filtered['invoices']->pluck('opening')->all());
        $this->assertSame([4000, 8000], $filtered['invoices']->pluck('closing')->all());
        $this->assertSame([4000, 7000], $filtered['invoices']->pluck('current')->all());
        $this->assertSame(12000, $filtered['closing']);
        $this->assertSame(11000, $filtered['current']);
    }

    public function test_missing_or_duplicate_invoice_numbers_keep_separate_stable_groups(): void
    {
        $unnumbered = $this->order(10, 30, 0, '2026-01-01');
        $first = $this->order(1, 100, 0, '2026-01-01');
        $first->invoice_number = 'INV-2';
        $second = $this->order(2, 50, 0, '2026-01-01');
        $second->invoice_number = 'INV-2';
        $history = app(CustomerRemainingAmountReportService::class)->history(collect([$second, $unnumbered, $first]));
        $this->assertSame(['#10', 'INV-2', 'INV-2'], $history['invoices']->pluck('invoice')->all());
        $this->assertSame([10, 1, 2], $history['invoices']->pluck('order_id')->all());
        $this->assertSame([3000, 10000, 5000], $history['invoices']->pluck('closing')->all());
    }

    public function test_history_sets_status_on_invoices_and_transaction_rows(): void
    {
        $order = $this->order(1, 100, 30, '2026-01-01', [
            ['id' => 1, 'amount' => 30, 'payment_date' => '2026-01-02'],
        ]);
        $order->payment_status = 'Partial';
        $history = app(CustomerRemainingAmountReportService::class)->history(collect([$order]));

        $this->assertSame('Partial', $history['invoices']->first()['payment_status']);
        $rows = $history['invoices']->first()['rows'];
        $this->assertCount(2, $rows);
        $this->assertSame('order', $rows[0]['type']);
        $this->assertSame('Partial', $rows[0]['status']);
        $this->assertSame('payment', $rows[1]['type']);
        $this->assertSame('Paid', $rows[1]['status']);
    }
}
