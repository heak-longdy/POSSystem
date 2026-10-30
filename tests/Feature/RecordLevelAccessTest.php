<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderPayment;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SelectController;
use App\Services\OrderBulkPrintService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class RecordLevelAccessTest extends TestCase
{
    protected User $adminUser;
    protected User $regularUser1;
    protected User $regularUser2;
    protected Shop $shop;

    protected function setUp(): void
    {
        parent::setUp();

        require_once database_path('migrations/2026_08_02_231335_create_permission_tables.php');
        (new \CreatePermissionTables())->up();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('role')->nullable();
            $table->string('data_access')->default('own');
            $table->string('language_preference')->default('en');
            $table->integer('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('image')->nullable();
            $table->double('price')->default(10);
            $table->double('cost')->default(5);
            $table->unsignedBigInteger('category_id')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->integer('ordering')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('profile')->nullable();
            $table->string('password')->nullable();
            $table->integer('total_point')->default(0);
            $table->string('user')->nullable();
            $table->integer('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('shop_id')->nullable();
            $table->unsignedBigInteger('barber_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
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
            $table->text('remark')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('service_id')->nullable();
            $table->string('type')->default('product');
            $table->integer('qty')->default(1);
            $table->double('price')->default(0);
            $table->double('product_discount')->default(0);
            $table->double('commission')->default(0);
            $table->integer('point')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->double('amount')->default(0);
            $table->string('payment_method')->default('Cash');
            $table->dateTime('payment_date')->nullable();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('customer_paid', function (Blueprint $table) {
            $table->id();
            $table->double('amount_usd')->default(0);
            $table->double('amount_kh')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('barbers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('staffs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->integer('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        (new PermissionSeeder())->run();

        // User with data_access = 'all' (can view all records store-wide)
        $this->adminUser = User::create([
            'name' => 'All Access User',
            'email' => 'admin@test.local',
            'role' => 'admin',
            'data_access' => 'all',
        ]);

        // Users with data_access = 'own' (restricted to records they created)
        $this->regularUser1 = User::create([
            'name' => 'Restricted User 1',
            'email' => 'cashier1@test.local',
            'role' => 'cashier',
            'data_access' => 'own',
        ]);
        $this->regularUser2 = User::create([
            'name' => 'Restricted User 2',
            'email' => 'cashier2@test.local',
            'role' => 'cashier',
            'data_access' => 'own',
        ]);

        $allPermissions = [
            'customer-view', 'customer-create', 'customer-update', 'customer-delete',
            'order-view', 'order-create', 'order-update', 'order-delete', 'dashboard-view',
        ];

        $this->adminUser->syncPermissions($allPermissions);
        $this->regularUser1->syncPermissions($allPermissions);
        $this->regularUser2->syncPermissions($allPermissions);

        $this->shop = Shop::create(['name' => 'Main Shop']);
    }

    public function test_user_data_access_helper_and_attributes(): void
    {
        $superAdmin = User::create(['name' => 'Super', 'email' => 'super@test.local', 'role' => 'super_admin', 'data_access' => 'own']);
        $managerAll = User::create(['name' => 'Manager All', 'email' => 'mgr@test.local', 'role' => 'manager', 'data_access' => 'all']);
        $adminRestricted = User::create(['name' => 'Admin Own', 'email' => 'admin_own@test.local', 'role' => 'admin', 'data_access' => 'own']);
        $staffOwn = User::create(['name' => 'Staff Own', 'email' => 'staff@test.local', 'role' => 'staff', 'data_access' => 'own']);

        // super_admin always has full access unconditionally
        $this->assertTrue($superAdmin->hasFullDataAccess());

        // Users with data_access = 'all' have full access regardless of role
        $this->assertTrue($managerAll->hasFullDataAccess());
        $this->assertTrue($this->adminUser->hasFullDataAccess());

        // Users with data_access = 'own' are restricted (even if role is admin)
        $this->assertFalse($adminRestricted->hasFullDataAccess());
        $this->assertFalse($staffOwn->hasFullDataAccess());
        $this->assertFalse($this->regularUser1->hasFullDataAccess());

        // Test label and badge accessors
        $this->assertEquals('All Records', $this->adminUser->data_access_label);
        $this->assertEquals('Own Records Only', $this->regularUser1->data_access_label);
        $this->assertStringContainsString('All Records', $this->adminUser->data_access_badge);
        $this->assertStringContainsString('Own Records Only', $this->regularUser1->data_access_badge);
    }

    public function test_customer_model_ownership_and_query_scope(): void
    {
        $cust1 = Customer::create(['name' => 'Customer of User 1', 'user' => (string) $this->regularUser1->id, 'status' => 1]);
        $cust2 = Customer::create(['name' => 'Customer of User 2', 'user' => (string) $this->regularUser2->id, 'status' => 1]);

        // Direct isOwnedBy check
        $this->assertTrue($cust1->isOwnedBy($this->regularUser1));
        $this->assertFalse($cust1->isOwnedBy($this->regularUser2));
        $this->assertTrue($cust1->isOwnedBy($this->adminUser));
        $this->assertTrue($cust2->isOwnedBy($this->adminUser));

        // Query scope when authenticated as regular user
        $this->actingAs($this->regularUser1);
        $user1Customers = Customer::forUser()->pluck('id')->all();
        $this->assertContains($cust1->id, $user1Customers);
        $this->assertNotContains($cust2->id, $user1Customers);

        // Query scope when authenticated as admin
        $this->actingAs($this->adminUser);
        $adminCustomers = Customer::forUser()->pluck('id')->all();
        $this->assertContains($cust1->id, $adminCustomers);
        $this->assertContains($cust2->id, $adminCustomers);
    }

    public function test_order_model_ownership_and_query_scope(): void
    {
        $order1 = Order::create(['invoice_number' => 'INV-001', 'created_by' => $this->regularUser1->id, 'total_price' => 100]);
        $order2 = Order::create(['invoice_number' => 'INV-002', 'created_by' => $this->regularUser2->id, 'total_price' => 200]);

        // Direct isOwnedBy check
        $this->assertTrue($order1->isOwnedBy($this->regularUser1));
        $this->assertFalse($order1->isOwnedBy($this->regularUser2));
        $this->assertTrue($order1->isOwnedBy($this->adminUser));
        $this->assertTrue($order2->isOwnedBy($this->adminUser));

        // Query scope when authenticated as regular user
        $this->actingAs($this->regularUser1);
        $user1Orders = Order::forUser()->pluck('id')->all();
        $this->assertContains($order1->id, $user1Orders);
        $this->assertNotContains($order2->id, $user1Orders);

        // Query scope when authenticated as admin
        $this->actingAs($this->adminUser);
        $adminOrders = Order::forUser()->pluck('id')->all();
        $this->assertContains($order1->id, $adminOrders);
        $this->assertContains($order2->id, $adminOrders);
    }

    public function test_customer_select2_lookups_restricted_to_creator(): void
    {
        Customer::create(['name' => 'Alpha User 1', 'user' => (string) $this->regularUser1->id, 'status' => 1]);
        Customer::create(['name' => 'Beta User 2', 'user' => (string) $this->regularUser2->id, 'status' => 1]);

        $selectController = app(SelectController::class);

        // Regular user 1 search
        $this->actingAs($this->regularUser1);
        $req = new Request(['search' => 'User']);
        $response = $selectController->selectCustomer($req);
        $data = json_decode($response->getContent(), true)['data'];
        $names = array_column($data, 'name');
        $this->assertContains('Alpha User 1', $names);
        $this->assertNotContains('Beta User 2', $names);

        // Admin search
        $this->actingAs($this->adminUser);
        $responseAdmin = $selectController->selectCustomer($req);
        $dataAdmin = json_decode($responseAdmin->getContent(), true)['data'];
        $namesAdmin = array_column($dataAdmin, 'name');
        $this->assertContains('Alpha User 1', $namesAdmin);
        $this->assertContains('Beta User 2', $namesAdmin);
    }

    public function test_customer_controller_unauthorized_actions_trigger_403(): void
    {
        $cust2 = Customer::create(['name' => 'Customer 2', 'user' => (string) $this->regularUser2->id, 'status' => 1]);

        $this->actingAs($this->regularUser1);

        // Edit unowned customer -> 403
        try {
            $this->get(route('admin-customer-edit', $cust2->id));
            $this->fail('Expected 403 Forbidden for editing unowned customer');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        // Delete unowned customer -> 403
        try {
            $this->post(route('admin-customer-delete', $cust2->id));
            $this->fail('Expected 403 Forbidden for deleting unowned customer');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        // Update status unowned customer -> 403
        try {
            $this->get(route('admin-customer-status', ['id' => $cust2->id, 'status' => 2]));
            $this->fail('Expected 403 Forbidden for changing status of unowned customer');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        // Admin can edit customer 2 without 403
        $this->actingAs($this->adminUser);
        $response = $this->get(route('admin-customer-edit', $cust2->id));
        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_order_controller_unauthorized_actions_trigger_403(): void
    {
        $order2 = Order::create([
            'invoice_number' => 'INV-200',
            'created_by' => $this->regularUser2->id,
            'payment_status' => 'Pending',
            'total_price' => 100,
            'paid_amount' => 0,
            'shop_id' => $this->shop->id,
        ]);

        $this->actingAs($this->regularUser1);

        // Show unowned order -> 403
        try {
            $this->get(route('admin-order-detail', $order2->id));
            $this->fail('Expected 403 Forbidden for detail on unowned order');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        // Edit unowned order -> 403
        try {
            $this->get(route('admin-order-edit', $order2->id));
            $this->fail('Expected 403 Forbidden for edit on unowned order');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        // Delete unowned order -> 403
        try {
            $this->post(route('admin-order-delete', $order2->id));
            $this->fail('Expected 403 Forbidden for delete on unowned order');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        // Cancel unowned order -> 403
        try {
            $this->post(route('admin-order-cancel', $order2->id));
            $this->fail('Expected 403 Forbidden for cancel on unowned order');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        // Add payment to unowned order -> 403
        try {
            $this->post(route('admin-order-add-payment', $order2->id), ['amount' => 50]);
            $this->fail('Expected 403 Forbidden for addPayment on unowned order');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        // Admin can access order 2 without 403
        $this->actingAs($this->adminUser);
        $response = $this->get(route('admin-order-detail', $order2->id));
        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_bulk_print_rejects_unowned_orders_for_regular_user(): void
    {
        $order1 = Order::create(['invoice_number' => 'INV-001', 'created_by' => $this->regularUser1->id, 'shop_id' => $this->shop->id]);
        $order2 = Order::create(['invoice_number' => 'INV-002', 'created_by' => $this->regularUser2->id, 'shop_id' => $this->shop->id]);

        $service = app(OrderBulkPrintService::class);

        $this->actingAs($this->regularUser1);

        // Printing own order succeeds
        $result = $service->prepareForPreview([$order1->id], 'invoice', 'en');
        $this->assertCount(1, $result['orders']);

        // Printing other's order throws ValidationException
        $this->expectException(ValidationException::class);
        $service->prepareForPreview([$order1->id, $order2->id], 'invoice', 'en');
    }

    public function test_dashboard_isolates_metrics_for_regular_user_and_shows_global_for_admin(): void
    {
        Customer::create(['name' => 'C1', 'user' => (string) $this->regularUser1->id, 'status' => 1]);
        Customer::create(['name' => 'C2', 'user' => (string) $this->regularUser2->id, 'status' => 1]);

        Order::create([
            'invoice_number' => 'INV-U1',
            'created_by' => $this->regularUser1->id,
            'total_price' => 100,
            'paid_amount' => 30,
            'payment_status' => 'Partial',
            'order_date' => now(),
        ]);

        Order::create([
            'invoice_number' => 'INV-U2',
            'created_by' => $this->regularUser2->id,
            'total_price' => 500,
            'paid_amount' => 0,
            'payment_status' => 'Pending',
            'order_date' => now(),
        ]);

        // Regular user 1 dashboard (Own Records Only: Products, Staff, User, Recent Transactions, Product Performance not shown)
        $this->actingAs($this->regularUser1);
        $controller = app(DashboardController::class);
        $viewUser1 = $controller->index(new Request());
        $dataUser1 = $viewUser1->getData();

        $this->assertFalse($dataUser1['hasFullDataAccess']);
        $this->assertEquals(1, $dataUser1['totalCustomerCount']);
        $this->assertEquals(100.0, $dataUser1['totalOrderPrice']);
        $this->assertEquals(70.0, $dataUser1['totalOrderRemainingAmount']);
        $this->assertEquals(0, $dataUser1['pendingOrderCount']);
        $this->assertEquals(0, $dataUser1['totalProductCount']);
        $this->assertEquals(0, $dataUser1['totalStaffCount']);
        $this->assertEquals(0, $dataUser1['totalUserCount']);
        $this->assertCount(0, $dataUser1['recentTransactions']);
        $this->assertCount(0, $dataUser1['productPerformance']);

        // Admin user dashboard sees all
        $this->actingAs($this->adminUser);
        $viewAdmin = $controller->index(new Request());
        $dataAdmin = $viewAdmin->getData();

        $this->assertTrue($dataAdmin['hasFullDataAccess']);
        $this->assertEquals(2, $dataAdmin['totalCustomerCount']);
        $this->assertEquals(600.0, $dataAdmin['totalOrderPrice']);
        $this->assertEquals(570.0, $dataAdmin['totalOrderRemainingAmount']);
        $this->assertEquals(1, $dataAdmin['pendingOrderCount']);
        $this->assertCount(2, $dataAdmin['recentTransactions']);
    }
}
