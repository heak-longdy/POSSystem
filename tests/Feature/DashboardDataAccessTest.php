<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\DashboardController;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Staff;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DashboardDataAccessTest extends TestCase
{
    protected User $ownUser;
    protected User $allUser;
    protected User $superAdmin;

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
            $table->string('invoice_number')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('shop_id')->nullable();
            $table->unsignedBigInteger('barber_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->double('total_price')->default(0);
            $table->double('paid_amount')->default(0);
            $table->string('payment_status')->default('Pending');
            $table->dateTime('order_date')->nullable();
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

        Schema::create('staffs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->integer('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('barbers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        (new PermissionSeeder())->run();

        $this->ownUser = User::create([
            'name' => 'Own Records User',
            'email' => 'own@test.local',
            'role' => 'staff',
            'data_access' => 'own',
        ]);

        $this->allUser = User::create([
            'name' => 'All Records User',
            'email' => 'all@test.local',
            'role' => 'admin',
            'data_access' => 'all',
        ]);

        $this->superAdmin = User::create([
            'name' => 'Super Admin User',
            'email' => 'superadmin@test.local',
            'role' => 'super_admin',
            'data_access' => 'own', // Should still have full access because super_admin
        ]);
    }

    public function test_user_data_access_helpers(): void
    {
        $this->assertFalse($this->ownUser->hasFullDataAccess());
        $this->assertTrue($this->ownUser->hasOwnDataAccessOnly());

        $this->assertTrue($this->allUser->hasFullDataAccess());
        $this->assertFalse($this->allUser->hasOwnDataAccessOnly());

        $this->assertTrue($this->superAdmin->hasFullDataAccess());
        $this->assertFalse($this->superAdmin->hasOwnDataAccessOnly());
    }

    public function test_dashboard_controller_omits_restricted_metrics_for_own_records_user(): void
    {
        Product::create(['name' => 'Item 1', 'price' => 10, 'cost' => 5, 'status' => 1]);
        Staff::create(['name' => 'Staff 1', 'status' => 1]);
        Customer::create(['name' => 'Customer 1', 'user' => (string) $this->ownUser->id, 'status' => 1]);
        Order::create([
            'invoice_number' => 'INV-001',
            'created_by' => $this->ownUser->id,
            'total_price' => 50,
            'paid_amount' => 50,
            'payment_status' => 'Paid',
            'order_date' => now(),
        ]);

        $this->actingAs($this->ownUser);
        $controller = app(DashboardController::class);
        $view = $controller->index(new Request());
        $data = $view->getData();

        // Own records user should have hasFullDataAccess = false
        $this->assertFalse($data['hasFullDataAccess']);

        // Restricted metrics should be zero/empty
        $this->assertEquals(0, $data['totalProductCount']);
        $this->assertEquals(0, $data['totalProductsCount']);
        $this->assertEquals(0, $data['activeProductCount']);
        $this->assertEquals(0, $data['totalStaffCount']);
        $this->assertEquals(0, $data['activeStaffCount']);
        $this->assertEquals(0, $data['totalUserCount']);
        $this->assertEquals(0, $data['activeUserCount']);
        $this->assertCount(0, $data['recentTransactions']);
        $this->assertCount(0, $data['productPerformance']);
        $this->assertEmpty($data['productPerformanceMonths']);

        // Own metrics should be present
        $this->assertEquals(1, $data['totalCustomerCount']);
        $this->assertEquals(50.0, $data['totalOrderPrice']);
    }

    public function test_dashboard_controller_ajax_product_performance_returns_empty_for_own_records_user(): void
    {
        $this->actingAs($this->ownUser);
        $controller = app(DashboardController::class);

        $request = new Request(['ajax' => 'product_performance', 'product_month' => 'all']);
        $response = $controller->index($request);

        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertSame('', $content['html']);
    }

    public function test_dashboard_controller_loads_all_metrics_for_all_records_user(): void
    {
        Product::create(['name' => 'Item 1', 'price' => 10, 'cost' => 5, 'status' => 1]);
        Staff::create(['name' => 'Staff 1', 'status' => 1]);
        Order::create([
            'invoice_number' => 'INV-001',
            'created_by' => $this->ownUser->id,
            'total_price' => 50,
            'paid_amount' => 50,
            'payment_status' => 'Paid',
            'order_date' => now(),
        ]);

        $this->actingAs($this->allUser);
        $controller = app(DashboardController::class);
        $view = $controller->index(new Request());
        $data = $view->getData();

        $this->assertTrue($data['hasFullDataAccess']);
        $this->assertEquals(1, $data['totalProductCount']);
        $this->assertEquals(1, $data['totalStaffCount']);
        $this->assertCount(1, $data['recentTransactions']);
        $this->assertNotEmpty($data['productPerformanceMonths']);
    }

    public function test_dashboard_blade_view_hides_widgets_for_own_records_user(): void
    {
        $this->actingAs($this->ownUser);
        $controller = app(DashboardController::class);
        $view = $controller->index(new Request());
        $html = $view->render();

        // Must NOT show Products (Count), Staff (Count), User (Count), Recent Transactions, Product Performance
        $this->assertStringNotContainsString(__('dashboard.products_count'), $html);
        $this->assertStringNotContainsString(__('dashboard.staff_count'), $html);
        $this->assertStringNotContainsString(__('dashboard.user_count'), $html);
        $this->assertStringNotContainsString('<div class="dash-grid-row dash-grid-row-4">', $html);
        $this->assertStringNotContainsString('<div class="timeline-container">', $html);
        $this->assertStringNotContainsString('<div class="product-table-wrapper">', $html);
        $this->assertStringNotContainsString('<h3 class="dash-card-title">' . __('dashboard.recent_transactions') . '</h3>', $html);
        $this->assertStringNotContainsString('<h3 class="dash-card-title">' . __('dashboard.product_performance') . '</h3>', $html);

        // Must show Orders Total Price, Orders Pending, Orders Remaining, and Customers Count
        $this->assertStringContainsString(__('dashboard.orders_total_price'), $html);
        $this->assertStringContainsString(__('dashboard.orders_pending'), $html);
        $this->assertStringContainsString(__('dashboard.orders_remaining_amount'), $html);
        $this->assertStringContainsString(__('dashboard.customers_count'), $html);
        $this->assertStringContainsString(__('dashboard.orders_pending'), $html);
        $this->assertStringContainsString(__('dashboard.orders_remaining_amount'), $html);
        $this->assertStringContainsString(__('dashboard.customers_count'), $html);
    }

    public function test_dashboard_blade_view_shows_all_widgets_for_full_access_user(): void
    {
        $this->actingAs($this->allUser);
        $controller = app(DashboardController::class);
        $view = $controller->index(new Request());
        $html = $view->render();

        // Must show all widgets
        $this->assertStringContainsString(__('dashboard.products_count'), $html);
        $this->assertStringContainsString(__('dashboard.staff_count'), $html);
        $this->assertStringContainsString(__('dashboard.user_count'), $html);
        $this->assertStringContainsString(__('dashboard.recent_transactions'), $html);
        $this->assertStringContainsString(__('dashboard.product_performance'), $html);
        $this->assertStringContainsString(__('dashboard.orders_total_price'), $html);
        $this->assertStringContainsString(__('dashboard.customers_count'), $html);
    }
}
