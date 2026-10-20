<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderPayment;
use App\Models\Shop;
use App\Models\User;
use App\Services\InvoiceService;
use App\Services\OrderBulkPrintService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrderBulkPrintTest extends TestCase
{
    protected User $permittedUser;
    protected User $unpermittedUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        require_once database_path('migrations/2026_08_02_231335_create_permission_tables.php');
        (new \CreatePermissionTables())->up();
        (new \Database\Seeders\PermissionSeeder())->run();

        foreach (['users', 'customers', 'shops', 'services', 'barbers', 'products', 'uoms'] as $name) {
            Schema::create($name, function (Blueprint $table) use ($name) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('phone')->nullable();
                $table->string('code')->nullable();
                $table->string('city')->nullable();
                $table->string('address')->nullable();
                $table->string('nick_name')->nullable();
                $table->string('image_url')->nullable();
                $table->boolean('status')->default(true);
                if ($name === 'products') {
                    $table->integer('uom_id')->nullable();
                }
                $table->timestamps();
                $table->softDeletes();
            });
            DB::table($name)->insert(['id' => 1, 'name' => 'Test ' . $name]);
        }

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id')->nullable();
            $table->integer('shop_id')->nullable();
            $table->integer('barber_id')->nullable();
            $table->double('total_price')->default(0);
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
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_payments', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id');
            $table->double('amount');
            $table->string('payment_method')->nullable();
            $table->dateTime('payment_date')->nullable();
            $table->text('note')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id');
            $table->integer('service_id')->nullable();
            $table->integer('product_id')->nullable();
            $table->double('price');
            $table->integer('qty');
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

        // Users
        $this->permittedUser = User::forceCreate(['name' => 'Permitted User', 'status' => 1]);
        $this->permittedUser->givePermissionTo('order-bulk-print-view');

        $this->unpermittedUser = User::forceCreate(['name' => 'Unpermitted User', 'status' => 1]);
    }

    private function createOrder(array $attributes = []): Order
    {
        return Order::create(array_merge([
            'shop_id' => 1,
            'customer_id' => 1,
            'total_price' => 100.00,
            'paid_amount' => 0.00,
            'payment_status' => 'Pending',
            'order_date' => now()->format('Y-m-d H:i:s'),
            'invoice_number' => 'INV-' . rand(1000, 9999),
        ], $attributes));
    }

    public function test_unauthenticated_users_are_redirected_to_login(): void
    {
        $this->get(route('admin-order-bulk-print-index'))->assertRedirect();
        $this->post(route('admin-order-bulk-print-selection'))->assertRedirect();
        $this->post(route('admin-order-bulk-print-preview'))->assertRedirect();
    }

    public function test_users_without_order_bulk_print_view_permission_are_forbidden(): void
    {
        $this->actingAs($this->unpermittedUser);
        $this->get(route('admin-order-bulk-print-index'))->assertForbidden();
        $this->post(route('admin-order-bulk-print-selection'))->assertForbidden();
        $this->post(route('admin-order-bulk-print-preview'))->assertForbidden();
    }

    public function test_users_with_only_order_view_cannot_access_order_bulk_print(): void
    {
        $orderOnlyUser = User::forceCreate(['name' => 'Order Only User', 'status' => 1]);
        $orderOnlyUser->givePermissionTo('order-view');

        $this->actingAs($orderOnlyUser);
        $this->get(route('admin-order-bulk-print-index'))->assertForbidden();
        $this->post(route('admin-order-bulk-print-selection'))->assertForbidden();
        $this->post(route('admin-order-bulk-print-preview'))->assertForbidden();
    }

    public function test_permitted_users_can_view_bulk_print_index(): void
    {
        $order = $this->createOrder(['invoice_number' => 'INV-TEST-001']);

        $response = $this->actingAs($this->permittedUser)
            ->get(route('admin-order-bulk-print-index'))
            ->assertOk();

        $response->assertSee('INV-TEST-001');
        $response->assertSee(route('admin-order-bulk-print-preview'));
    }

    public function test_selection_returns_matching_order_ids_and_obeys_filters(): void
    {
        $order1 = $this->createOrder(['payment_status' => 'Pending']);
        $order2 = $this->createOrder(['payment_status' => 'Paid']);
        $order3 = $this->createOrder(['payment_status' => 'Cancel']);

        $this->actingAs($this->permittedUser);

        // Filter Pending
        $res = $this->postJson(route('admin-order-bulk-print-selection', ['status' => 'Pending']));
        $res->assertOk()->assertJson([
            'success' => true,
            'count' => 1,
            'ids' => [$order1->id],
        ]);

        // Filter Paid
        $res = $this->postJson(route('admin-order-bulk-print-selection', ['status' => 'Paid']));
        $res->assertOk()->assertJson([
            'success' => true,
            'count' => 1,
            'ids' => [$order2->id],
        ]);

        // All status excludes Cancel
        $res = $this->postJson(route('admin-order-bulk-print-selection', ['status' => 'all']));
        $res->assertOk()->assertJson([
            'success' => true,
            'count' => 2,
        ]);
        $this->assertContains($order1->id, $res->json('ids'));
        $this->assertContains($order2->id, $res->json('ids'));
        $this->assertNotContains($order3->id, $res->json('ids'));
    }

    public function test_selection_rejects_when_over_limit(): void
    {
        config(['order_printing.max_orders' => 2]);

        $this->createOrder();
        $this->createOrder();
        $this->createOrder(); // 3 orders > limit 2

        $this->actingAs($this->permittedUser);
        $res = $this->postJson(route('admin-order-bulk-print-selection', ['status' => 'all']));
        $res->assertStatus(422)
            ->assertJson([
                'success' => false,
                'over_limit' => true,
                'total_count' => 3,
                'limit' => 2,
            ]);
    }

    public function test_preview_validates_required_parameters(): void
    {
        $this->actingAs($this->permittedUser);

        // Empty payload
        $this->post(route('admin-order-bulk-print-preview'), [])
            ->assertSessionHasErrors(['document_type', 'order_ids']);

        // Invalid document type
        $this->post(route('admin-order-bulk-print-preview'), [
            'document_type' => 'malicious_template',
            'print_locale' => 'en',
            'order_ids' => [1],
        ])->assertSessionHasErrors(['document_type']);

        // Invalid locale
        $this->post(route('admin-order-bulk-print-preview'), [
            'document_type' => 'invoice',
            'print_locale' => 'fr',
            'order_ids' => [1],
        ])->assertSessionHasErrors(['print_locale']);
    }

    public function test_preview_renders_invoices_and_delivery_slips_successfully(): void
    {
        $order1 = $this->createOrder(['invoice_number' => 'INV-001', 'total_price' => 50.00]);
        $order2 = $this->createOrder(['invoice_number' => 'INV-002', 'total_price' => 75.00]);

        $this->actingAs($this->permittedUser);

        // Test Invoice preview in Khmer
        $responseKm = $this->post(route('admin-order-bulk-print-preview'), [
            'document_type' => 'invoice',
            'print_locale' => 'km',
            'order_ids' => [$order1->id, $order2->id],
        ])->assertOk();

        $responseKm->assertSee('INV-001');
        $responseKm->assertSee('INV-002');
        $responseKm->assertSee('វិក្កយបត្រ'); // Khmer invoice title
        $responseKm->assertSee('bulk_print_invoice_' . $order1->id . '_km');

        // Test Delivery Slip preview in English
        $responseEn = $this->post(route('admin-order-bulk-print-preview'), [
            'document_type' => 'delivery_slip',
            'print_locale' => 'en',
            'order_ids' => [$order1->id, $order2->id],
        ])->assertOk();

        $responseEn->assertSee('INV-001');
        $responseEn->assertSee('INV-002');
        $responseEn->assertSee('Delivery Slip');

        // Test GET preview request as fetched by MDI tab manager
        $responseGet = $this->get(route('admin-order-bulk-print-preview', [
            'document_type' => 'invoice',
            'order_ids' => "{$order1->id},{$order2->id}",
            'print_locale' => 'km',
        ]))->assertOk();

        $responseGet->assertSee('header-name-meta', false);
        $responseGet->assertSee('workspace-viewport', false);
        $responseGet->assertSee('window.MDI.closeTab', false);
        $responseGet->assertSee('orderBulkPrintPreview', false);
        $responseGet->assertSee('size: A5 landscape', false);
        $responseGet->assertSee('page-break-before: always', false);
    }

    public function test_previous_balance_equivalence_between_single_and_batch(): void
    {
        $invoiceService = app(InvoiceService::class);

        // Customer 1 has 3 orders: 2 Pending, 1 Partial
        $c1Order1 = $this->createOrder(['customer_id' => 1, 'payment_status' => 'Pending', 'total_price' => 100, 'paid_amount' => 0]);
        $c1Order2 = $this->createOrder(['customer_id' => 1, 'payment_status' => 'Partial', 'total_price' => 50, 'paid_amount' => 20]); // unpaid 30
        $c1Order3 = $this->createOrder(['customer_id' => 1, 'payment_status' => 'Pending', 'total_price' => 40, 'paid_amount' => 0]);

        // Customer 2 has 1 Paid order, 1 Pending
        $c2Order1 = $this->createOrder(['customer_id' => 2, 'payment_status' => 'Paid', 'total_price' => 80, 'paid_amount' => 80]);
        $c2Order2 = $this->createOrder(['customer_id' => 2, 'payment_status' => 'Pending', 'total_price' => 60, 'paid_amount' => 10]); // unpaid 50

        // Walk-in order (no customer)
        $walkIn = $this->createOrder(['customer_id' => null, 'payment_status' => 'Pending', 'total_price' => 25, 'paid_amount' => 0]);

        $allOrders = [$c1Order1, $c1Order2, $c1Order3, $c2Order1, $c2Order2, $walkIn];

        $batchBalances = $invoiceService->previousBalanceForMany($allOrders);

        foreach ($allOrders as $order) {
            $singleBalance = $invoiceService->previousBalance($order);
            $batchBalance = $batchBalances[$order->id];

            $this->assertEquals(
                $singleBalance,
                $batchBalance,
                "Order #{$order->id} single balance ({$singleBalance}) does not match batch balance ({$batchBalance})"
            );
        }
    }

    public function test_batch_service_rejects_missing_order_or_excessive_detail_rows(): void
    {
        $service = app(OrderBulkPrintService::class);

        $order = $this->createOrder();

        // Missing order ID in selection
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $service->prepareForPreview([$order->id, 99999], 'invoice', 'en');
    }
}
