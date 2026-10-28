<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderBalanceTransfer;
use App\Models\OrderPayment;
use App\Models\Shop;
use App\Models\User;
use App\Services\CustomerRemainingAmountReportService;
use App\Services\OrderBalanceService;
use App\Services\OrderBalanceTransferService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrderPreviousBalanceManagementTest extends TestCase
{
    protected User $user;
    protected Shop $shop;
    protected Customer $customer;
    protected OrderBalanceTransferService $transferService;

    protected function setUp(): void
    {
        parent::setUp();

        require_once database_path('migrations/2026_08_02_231335_create_permission_tables.php');
        (new \CreatePermissionTables())->up();

        foreach (['users', 'customers', 'shops', 'services', 'barbers', 'products'] as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('phone')->nullable();
                $table->string('role')->nullable();
                $table->boolean('status')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('shop_id')->nullable();
            $table->unsignedBigInteger('barber_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->double('total_price')->default(0);
            $table->double('previous_balance')->default(0);
            $table->double('paid_amount')->default(0);
            $table->double('total_discount')->default(0);
            $table->double('total_commission')->default(0);
            $table->integer('total_point')->default(0);
            $table->string('payment_status')->default('Pending');
            $table->string('invoice_number')->nullable();
            $table->string('pay_way')->nullable();
            $table->dateTime('order_date')->nullable();
            $table->dateTime('delivery_date')->nullable();
            $table->dateTime('payment_date')->nullable();
            $table->string('creation_request_key')->nullable();
            $table->string('creation_payload_hash')->nullable();
            $table->text('remark')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_balance_transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('new_order_id');
            $table->unsignedBigInteger('previous_order_id');
            $table->decimal('transferred_amount', 15, 2);
            $table->string('original_payment_status', 20)->default('Pending');
            $table->dateTime('transferred_at');
            $table->unsignedBigInteger('transferred_by')->nullable();
            $table->string('transfer_status', 20)->default('completed');
            $table->dateTime('reversed_at')->nullable();
            $table->unsignedBigInteger('reversed_by')->nullable();
            $table->string('reversal_reason', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->double('amount');
            $table->string('payment_method')->nullable();
            $table->dateTime('payment_date')->nullable();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('creation_request_key')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('service_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->double('price')->default(0);
            $table->integer('qty')->default(1);
            $table->integer('point')->default(0);
            $table->string('type')->default('product');
            $table->double('product_discount')->default(0);
            $table->string('product_discount_type')->nullable();
            $table->double('product_commission')->default(0);
            $table->string('product_commission_type')->nullable();
            $table->double('service_discount')->default(0);
            $table->string('service_discount_type')->nullable();
            $table->double('service_commission')->default(0);
            $table->string('service_commission_type')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $this->user = User::create([
            'name' => 'Admin Test',
            'role' => 'super_admin',
        ]);
        $this->shop = Shop::create(['name' => 'Main POS Shop']);
        $this->customer = Customer::create(['name' => 'John Doe', 'phone' => '012345678']);

        $this->actingAs($this->user);
        $this->transferService = app(OrderBalanceTransferService::class);
    }

    public function test_preview_previous_balance_returns_zero_when_customer_has_no_orders(): void
    {
        $preview = $this->transferService->previewPreviousBalance($this->customer->id);

        $this->assertSame(0.0, $preview['previous_balance']);
        $this->assertSame(0, $preview['previous_orders_count']);
        $this->assertCount(0, $preview['previous_orders']);
    }

    public function test_preview_previous_balance_sums_only_active_unpaid_orders(): void
    {
        // Order 1: Pending, unpaid $100
        $order1 = Order::create([
            'customer_id' => $this->customer->id,
            'shop_id' => $this->shop->id,
            'invoice_number' => 'INV-001',
            'total_price' => 100.00,
            'paid_amount' => 0.00,
            'payment_status' => 'Pending',
            'order_date' => now()->subDays(5),
        ]);

        // Order 2: Partial, $150 total, $50 paid -> $100 balance
        $order2 = Order::create([
            'customer_id' => $this->customer->id,
            'shop_id' => $this->shop->id,
            'invoice_number' => 'INV-002',
            'total_price' => 150.00,
            'paid_amount' => 50.00,
            'payment_status' => 'Partial',
            'order_date' => now()->subDays(3),
        ]);

        // Order 3: Paid, $80 total, $80 paid -> 0 balance
        Order::create([
            'customer_id' => $this->customer->id,
            'shop_id' => $this->shop->id,
            'invoice_number' => 'INV-003',
            'total_price' => 80.00,
            'paid_amount' => 80.00,
            'payment_status' => 'Paid',
            'order_date' => now()->subDays(2),
        ]);

        // Order 4: Canceled
        Order::create([
            'customer_id' => $this->customer->id,
            'shop_id' => $this->shop->id,
            'invoice_number' => 'INV-004',
            'total_price' => 50.00,
            'paid_amount' => 0.00,
            'payment_status' => 'Cancel',
            'order_date' => now()->subDay(),
        ]);

        $preview = $this->transferService->previewPreviousBalance($this->customer->id);

        $this->assertSame(200.0, $preview['previous_balance']); // 100 + 100
        $this->assertSame(2, $preview['previous_orders_count']);
        $this->assertCount(2, $preview['previous_orders']);

        $invoices = collect($preview['previous_orders'])->pluck('invoice_number')->all();
        $this->assertContains('INV-001', $invoices);
        $this->assertContains('INV-002', $invoices);
    }

    public function test_balance_consolidation_updates_source_orders_and_creates_transfer_records(): void
    {
        $order1 = Order::create([
            'customer_id' => $this->customer->id,
            'shop_id' => $this->shop->id,
            'invoice_number' => 'INV-101',
            'total_price' => 100.00,
            'paid_amount' => 0.00,
            'payment_status' => 'Pending',
            'order_date' => now()->subDays(3),
        ]);

        $order2 = Order::create([
            'customer_id' => $this->customer->id,
            'shop_id' => $this->shop->id,
            'invoice_number' => 'INV-102',
            'total_price' => 50.00,
            'paid_amount' => 0.00,
            'payment_status' => 'Pending',
            'order_date' => now()->subDays(2),
        ]);

        // Create new destination order with $200 sale
        $newOrder = Order::create([
            'customer_id' => $this->customer->id,
            'shop_id' => $this->shop->id,
            'invoice_number' => 'INV-103',
            'total_price' => 200.00,
            'previous_balance' => 0, // will be populated
            'paid_amount' => 0.00,
            'payment_status' => 'Pending',
            'order_date' => now(),
            'created_by' => $this->user->id,
        ]);

        $transferredTotal = $this->transferService->consolidateBalances($newOrder);

        $this->assertSame(150.0, $transferredTotal);
        $newOrder->refresh();
        $order1->refresh();
        $order2->refresh();

        // 1. Destination order assertions
        $this->assertSame(150.0, (float) $newOrder->previous_balance);
        $this->assertSame(200.0, (float) $newOrder->total_price); // Unchanged sale price!
        $this->assertSame(350.0, (float) $newOrder->total_amount_due); // 200 + 150
        $this->assertSame(350.0, (float) $newOrder->remaining_amount);
        $this->assertFalse($newOrder->isTransferred());

        // 2. Source orders assertions
        $this->assertSame('Paid', $order1->payment_status);
        $this->assertTrue($order1->isTransferred());
        $this->assertSame(100.0, (float) $order1->transferred_out_amount);
        $this->assertSame(0.0, (float) $order1->remaining_amount);
        $this->assertSame(100.0, (float) $order1->total_price); // Historical sale price preserved!
        $this->assertSame(0.0, (float) $order1->paid_amount); // No fake payment records!

        $this->assertSame('Paid', $order2->payment_status);
        $this->assertTrue($order2->isTransferred());
        $this->assertSame(50.0, (float) $order2->transferred_out_amount);
        $this->assertSame(0.0, (float) $order2->remaining_amount);

        // 3. Database records
        $this->assertDatabaseHas('order_balance_transfers', [
            'previous_order_id' => $order1->id,
            'new_order_id' => $newOrder->id,
            'transferred_amount' => 100.00,
            'transfer_status' => 'completed',
        ]);
        $this->assertDatabaseHas('order_balance_transfers', [
            'previous_order_id' => $order2->id,
            'new_order_id' => $newOrder->id,
            'transferred_amount' => 50.00,
            'transfer_status' => 'completed',
        ]);

        // 4. Verify preview for the same customer now returns 350.00 from ONLY newOrder
        $nextPreview = $this->transferService->previewPreviousBalance($this->customer->id);
        $this->assertSame(350.0, $nextPreview['previous_balance']);
        $this->assertSame(1, $nextPreview['previous_orders_count']);
        $this->assertSame('INV-103', $nextPreview['previous_orders'][0]['invoice_number']);
    }

    public function test_multi_generation_balance_consolidation(): void
    {
        // Generation 1: Order 1 ($100 unpaid)
        $gen1 = Order::create([
            'customer_id' => $this->customer->id,
            'shop_id' => $this->shop->id,
            'invoice_number' => 'GEN-1',
            'total_price' => 100.00,
            'paid_amount' => 0.00,
            'payment_status' => 'Pending',
            'order_date' => now()->subDays(5),
            'created_by' => $this->user->id,
        ]);

        // Generation 2: Order 2 ($50 sale + $100 previous balance = $150 due)
        $gen2 = Order::create([
            'customer_id' => $this->customer->id,
            'shop_id' => $this->shop->id,
            'invoice_number' => 'GEN-2',
            'total_price' => 50.00,
            'paid_amount' => 0.00,
            'payment_status' => 'Pending',
            'order_date' => now()->subDays(3),
            'created_by' => $this->user->id,
        ]);
        $this->transferService->consolidateBalances($gen2);
        $gen2->refresh();

        $this->assertSame(100.0, (float) $gen2->previous_balance);
        $this->assertSame(150.0, (float) $gen2->total_amount_due);

        // Make partial payment of $50 on Gen 2 (leaving $100 balance)
        $gen2->update([
            'paid_amount' => 50.00,
            'payment_status' => 'Partial',
        ]);
        $gen2->refresh();
        $this->assertSame(100.0, (float) $gen2->remaining_amount);

        // Generation 3: Order 3 ($70 sale)
        $preview = $this->transferService->previewPreviousBalance($this->customer->id);
        // Should only pick up Gen 2 ($100), NEVER Gen 1 again!
        $this->assertSame(100.0, $preview['previous_balance']);
        $this->assertSame(1, $preview['previous_orders_count']);
        $this->assertSame('GEN-2', $preview['previous_orders'][0]['invoice_number']);

        $gen3 = Order::create([
            'customer_id' => $this->customer->id,
            'shop_id' => $this->shop->id,
            'invoice_number' => 'GEN-3',
            'total_price' => 70.00,
            'paid_amount' => 0.00,
            'payment_status' => 'Pending',
            'order_date' => now(),
            'created_by' => $this->user->id,
        ]);
        $this->transferService->consolidateBalances($gen3);
        $gen3->refresh();
        $gen2->refresh();
        $gen1->refresh();

        $this->assertSame(100.0, (float) $gen3->previous_balance);
        $this->assertSame(70.0, (float) $gen3->total_price);
        $this->assertSame(170.0, (float) $gen3->total_amount_due);
        $this->assertSame(170.0, (float) $gen3->remaining_amount);

        // Gen 2 is now marked Paid (transferred)
        $this->assertSame('Paid', $gen2->payment_status);
        $this->assertTrue($gen2->isTransferred());
        $this->assertSame(100.0, (float) $gen2->transferred_out_amount);
        $this->assertSame(0.0, (float) $gen2->remaining_amount);

        // Gen 1 remains Paid (transferred) from before
        $this->assertSame('Paid', $gen1->payment_status);
        $this->assertTrue($gen1->isTransferred());
        $this->assertSame(0.0, (float) $gen1->remaining_amount);
    }

    public function test_canceling_order_reverses_balance_transfers_and_restores_source_orders(): void
    {
        $sourceOrder = Order::create([
            'customer_id' => $this->customer->id,
            'shop_id' => $this->shop->id,
            'invoice_number' => 'RESTORE-SRC',
            'total_price' => 120.00,
            'paid_amount' => 20.00,
            'payment_status' => 'Partial',
            'order_date' => now()->subDay(),
            'created_by' => $this->user->id,
        ]);

        $newOrder = Order::create([
            'customer_id' => $this->customer->id,
            'shop_id' => $this->shop->id,
            'invoice_number' => 'RESTORE-NEW',
            'total_price' => 200.00,
            'paid_amount' => 0.00,
            'payment_status' => 'Pending',
            'order_date' => now(),
            'created_by' => $this->user->id,
        ]);

        $this->transferService->consolidateBalances($newOrder);
        $sourceOrder->refresh();
        $newOrder->refresh();

        $this->assertSame('Paid', $sourceOrder->payment_status);
        $this->assertSame(100.0, (float) $sourceOrder->transferred_out_amount);

        // Now cancel newOrder and reverse transfers
        $this->transferService->reverseTransfersForOrder($newOrder, 'Cancelled during test');

        $sourceOrder->refresh();
        $newOrder->refresh();

        // Source order is restored to Partial with its $100 remaining balance!
        $this->assertSame('Partial', $sourceOrder->payment_status);
        $this->assertFalse($sourceOrder->isTransferred());
        $this->assertSame(0.0, (float) $sourceOrder->transferred_out_amount);
        $this->assertSame(100.0, (float) $sourceOrder->remaining_amount);

        // Transfer status marked reversed
        $this->assertDatabaseHas('order_balance_transfers', [
            'previous_order_id' => $sourceOrder->id,
            'new_order_id' => $newOrder->id,
            'transfer_status' => 'reversed',
        ]);
    }

    public function test_customer_remaining_amount_report_accurately_reflects_consolidated_balances(): void
    {
        $order1 = Order::create([
            'customer_id' => $this->customer->id,
            'shop_id' => $this->shop->id,
            'invoice_number' => 'RPT-1',
            'total_price' => 100.00,
            'paid_amount' => 0.00,
            'payment_status' => 'Pending',
            'order_date' => now()->subDays(2),
            'created_by' => $this->user->id,
        ]);

        $order2 = Order::create([
            'customer_id' => $this->customer->id,
            'shop_id' => $this->shop->id,
            'invoice_number' => 'RPT-2',
            'total_price' => 200.00,
            'paid_amount' => 0.00,
            'payment_status' => 'Pending',
            'order_date' => now(),
            'created_by' => $this->user->id,
        ]);

        $this->transferService->consolidateBalances($order2);

        $reportService = app(CustomerRemainingAmountReportService::class);
        $rows = $reportService->customers([])->get();

        // Customer's outstanding balance should be exactly 300.00 ($200 current sale + $100 previous balance), NEVER $400!
        $customerRow = $rows->firstWhere('customer_id', $this->customer->id);
        $this->assertNotNull($customerRow);
        $this->assertSame(300.0, (float) $customerRow->remaining_amount);
        $this->assertSame(300.0, (float) $customerRow->total_amount);
    }

    public function test_order_index_paid_tab_renders_with_transferred_and_paid_orders(): void
    {
        // Source order transferred into newOrder
        $sourceOrder = Order::create([
            'customer_id' => $this->customer->id,
            'shop_id' => $this->shop->id,
            'invoice_number' => 'PAID-TAB-SRC',
            'total_price' => 50.00,
            'paid_amount' => 0.00,
            'payment_status' => 'Pending',
            'order_date' => now()->subDay(),
            'created_by' => $this->user->id,
        ]);

        $destOrder = Order::create([
            'customer_id' => $this->customer->id,
            'shop_id' => $this->shop->id,
            'invoice_number' => 'PAID-TAB-DEST',
            'total_price' => 100.00,
            'paid_amount' => 0.00,
            'payment_status' => 'Pending',
            'order_date' => now(),
            'created_by' => $this->user->id,
        ]);

        $this->transferService->consolidateBalances($destOrder);

        // Standard fully paid order
        $paidOrder = Order::create([
            'customer_id' => $this->customer->id,
            'shop_id' => $this->shop->id,
            'invoice_number' => 'PAID-REGULAR',
            'total_price' => 80.00,
            'paid_amount' => 80.00,
            'payment_status' => 'Paid',
            'order_date' => now(),
            'created_by' => $this->user->id,
        ]);

        // Request Order index page with paid status tab
        $response = $this->get('/admin/order/list/paid')->assertOk();

        // Ensure page displays and decorates rows without RelationNotFoundException
        $response->assertSee('PAID-TAB-SRC');
        $response->assertSee('PAID-REGULAR');

        // Request Remaining Amount index page with paid status tab
        $remResponse = $this->get('/admin/remaining-amount/list/paid')->assertOk();
        $remResponse->assertSee('PAID-TAB-SRC');
        $remResponse->assertSee('PAID-REGULAR');

        // Request Order detail page for transferred source order (renders notice and link to dest order)
        $detailSrcResponse = $this->get(route('admin-order-detail', $sourceOrder->id))->assertOk();
        $detailSrcResponse->assertSee('#' . $destOrder->invoice_number);

        // Request Order detail page for destination order (renders previous orders consolidated table)
        $detailDestResponse = $this->get(route('admin-order-detail', $destOrder->id))->assertOk();
        $detailDestResponse->assertSee('PAID-TAB-SRC');

        // Request Remaining Amount detail page for source order
        $remDetailSrcResponse = $this->get(route('admin-remaining-amount-detail', $sourceOrder->id))->assertOk();
        $remDetailSrcResponse->assertSee('#' . $destOrder->invoice_number);

        // Request Remaining Amount detail page for destination order
        $remDetailDestResponse = $this->get(route('admin-remaining-amount-detail', $destOrder->id))->assertOk();
        $remDetailDestResponse->assertSee('PAID-TAB-SRC');
    }
}
