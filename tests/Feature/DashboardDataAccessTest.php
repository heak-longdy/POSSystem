<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\DashboardController;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Staff;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
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

    private function dashboardOrder(string $invoice, ?string $date, string $status, float $total, float $paid = 0, array $extra = []): Order
    {
        $order = Order::create(array_merge([
            'invoice_number' => $invoice,
            'created_by' => $this->ownUser->id,
            'order_date' => $date,
            'payment_status' => $status,
            'total_price' => $total,
            'paid_amount' => $paid,
        ], $extra));
        if (isset($extra['created_at'])) {
            $order->forceFill(['created_at' => $extra['created_at']])->save();
        }
        return $order;
    }

    private function dashboardData(array $filters = []): array
    {
        return app(DashboardController::class)->index(new Request($filters))->getData();
    }

    public function test_custom_range_includes_full_days_and_preserves_existing_formulas_and_counts(): void
    {
        $this->actingAs($this->allUser);
        Product::create(['name' => 'Catalog item', 'status' => 1]);
        Customer::create(['name' => 'Customer', 'user' => (string) $this->ownUser->id, 'status' => 1]);
        Staff::create(['name' => 'Staff', 'status' => 1]);
        $first = $this->dashboardOrder('START', '2026-10-01 00:00:00', 'Pending', 100);
        $last = $this->dashboardOrder('END', '2026-10-31 23:59:59', 'Partial', 80, 30);
        // Fractional seconds must also be included; bypass Eloquent timestamp formatting.
        DB::table('orders')->where('id', $last->id)->update(['order_date' => '2026-10-31 23:59:59.999999']);
        $fallback = $this->dashboardOrder('FALLBACK', null, 'Paid', 40, 40, ['created_at' => '2026-10-15 10:00:00']);
        $cancel = $this->dashboardOrder('CANCEL', '2026-10-10', 'Cancel', 20);
        $overpaid = $this->dashboardOrder('OVERPAID', '2026-10-11', 'Partial', 10, 20);
        $this->dashboardOrder('BEFORE', '2026-09-30 23:59:59', 'Pending', 900);
        $this->dashboardOrder('AFTER', '2026-11-01 00:00:00', 'Pending', 800);
        $this->dashboardOrder('ORDER-DATE-WINS', '2026-09-20', 'Pending', 700, 0, ['created_at' => '2026-10-15 10:00:00']);
        $this->dashboardOrder('UNDATED-OUTSIDE', null, 'Pending', 600, 0, ['created_at' => '2026-11-01 00:00:00']);
        $this->dashboardOrder('DELETED', '2026-10-12', 'Pending', 500)->delete();

        $default = $this->dashboardData();
        $this->assertSame(now()->startOfMonth()->toDateString(), $default['dashboardFromDate']);
        $this->assertSame(now()->endOfMonth()->toDateString(), $default['dashboardToDate']);
        $this->assertFalse($default['dashboardAllTime']);

        $allTime = $this->dashboardData(['all_time' => 1]);
        $this->assertNull($allTime['dashboardFromDate']);
        $this->assertNull($allTime['dashboardToDate']);
        $this->assertTrue($allTime['dashboardAllTime']);

        $data = $this->dashboardData(['from_date' => '2026-10-01', 'to_date' => '2026-10-31']);
        $this->assertEquals(250, $data['totalOrderPrice']); // Existing total includes cancelled orders.
        $this->assertEquals(1, $data['pendingOrderCount']);
        $this->assertEquals(150, $data['totalOrderRemainingAmount']);
        $this->assertEquals(2, $data['remainingOrderCount']);
        $this->assertSame([$overpaid->id, $cancel->id, $fallback->id, $last->id, $first->id], $data['recentTransactions']->modelKeys());
        foreach (['totalProductCount', 'activeProductCount', 'totalCustomerCount', 'activeCustomerCount', 'totalStaffCount', 'activeStaffCount', 'totalUserCount', 'activeUserCount'] as $key) {
            $this->assertSame($allTime[$key], $data[$key], $key);
        }
        $reset = $this->dashboardData(['from_date' => '', 'to_date' => '']);
        $this->assertSame($allTime['totalOrderPrice'], $reset['totalOrderPrice']);
        $this->assertSame($allTime['totalOrderRemainingAmount'], $reset['totalOrderRemainingAmount']);
        $this->assertSame($allTime['recentTransactions']->modelKeys(), $reset['recentTransactions']->modelKeys());
    }

    public function test_dashboard_defaults_to_this_month_filter(): void
    {
        $this->actingAs($this->allUser);
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();

        $this->dashboardOrder('THIS-MONTH', now()->toDateString(), 'Paid', 300);
        $this->dashboardOrder('LAST-YEAR', now()->subYear()->toDateString(), 'Paid', 500);

        $default = $this->dashboardData();
        $this->assertSame($startOfMonth, $default['dashboardFromDate']);
        $this->assertSame($endOfMonth, $default['dashboardToDate']);
        $this->assertFalse($default['dashboardAllTime']);
        $this->assertEquals(300, $default['totalOrderPrice']);

        $allTime = $this->dashboardData(['all_time' => 1]);
        $this->assertNull($allTime['dashboardFromDate']);
        $this->assertNull($allTime['dashboardToDate']);
        $this->assertTrue($allTime['dashboardAllTime']);
        $this->assertEquals(800, $allTime['totalOrderPrice']);
    }

    public function test_product_month_intersects_custom_dates_and_empty_ranges_do_not_use_catalog_fallback(): void
    {
        $this->actingAs($this->allUser);
        $product = Product::create(['name' => 'Sold product', 'price' => 20, 'cost' => 5, 'status' => 1]);
        $catalog = Product::create(['name' => 'Unsold catalog item', 'price' => 99, 'status' => 1]);
        foreach ([['SEP', '2026-09-30', 2, 5], ['OCT', '2026-10-01', 3, 10], ['NOV', '2026-11-01', 4, 0], ['CANCELLED', '2026-10-02', 10, 0]] as [$invoice, $date, $qty, $discount]) {
            $order = $this->dashboardOrder($invoice, $date, $invoice === 'CANCELLED' ? 'Cancel' : 'Paid', 100);
            OrderDetail::create(['order_id' => $order->id, 'product_id' => $product->id, 'qty' => $qty, 'price' => 20, 'product_discount' => $discount]);
        }
        $range = ['from_date' => '2026-09-30', 'to_date' => '2026-10-31'];
        $all = $this->dashboardData($range)['productPerformance'];
        $this->assertCount(1, $all);
        $this->assertEquals(85, $all->first()['revenue']);
        $this->assertEquals(5, $all->first()['units_sold']);
        $this->assertSame('$85.00', $all->first()['budget']);
        $october = $this->dashboardData($range + ['product_month' => '2026-10'])['productPerformance'];
        $this->assertEquals(50, $october->first()['revenue']);
        $this->assertEquals(3, $october->first()['units_sold']);
        $this->assertCount(0, $this->dashboardData($range + ['product_month' => '2026-11'])['productPerformance']);
        $this->assertTrue($this->dashboardData(['all_time' => 1])['productPerformance']->contains('product_id', $catalog->id));

        $empty = $this->dashboardData(['from_date' => '2025-01-01', 'to_date' => '2025-01-01']);
        $this->assertEquals(0, $empty['totalOrderPrice']);
        $this->assertEquals(0, $empty['pendingOrderCount']);
        $this->assertEquals(0, $empty['totalOrderRemainingAmount']);
        $this->assertEmpty($empty['recentTransactions']);
        $this->assertEmpty($empty['productPerformance']);
    }

    public function test_product_performance_uses_creation_date_fallback_and_displays_zero_sales_as_zero(): void
    {
        $this->actingAs($this->allUser);
        $product = Product::create(['name' => 'Free product', 'price' => 25, 'status' => 1]);
        $order = $this->dashboardOrder('FREE', null, 'Paid', 0, 0, ['created_at' => '2026-10-31 23:59:59']);
        OrderDetail::create(['order_id' => $order->id, 'product_id' => $product->id, 'qty' => 1, 'price' => 25, 'product_discount' => 25]);
        $filters = ['from_date' => '2026-10-31', 'to_date' => '2026-10-31', 'product_month' => '2026-10'];
        $data = $this->dashboardData($filters);
        $this->assertCount(1, $data['recentTransactions']);
        $this->assertCount(1, $data['productPerformance']);
        $this->assertSame('$0.00', $data['productPerformance']->first()['budget']);
        $response = app(DashboardController::class)->index(new Request($filters + ['ajax' => 'product_performance']));
        $this->assertStringContainsString('$0.00', $response->getData(true)['html']);
        $this->assertStringNotContainsString('$25.00', $response->getData(true)['html']);
    }

    public function test_custom_range_keeps_own_record_scope_and_ajax_widget_restrictions(): void
    {
        $this->actingAs($this->ownUser);
        $this->dashboardOrder('OWN', '2026-10-01', 'Pending', 50);
        $this->dashboardOrder('OTHER', '2026-10-01', 'Pending', 900, 0, ['created_by' => $this->allUser->id]);
        $this->dashboardOrder('OUTSIDE', '2026-09-01', 'Pending', 800);
        $filters = ['from_date' => '2026-10-01', 'to_date' => '2026-10-01'];
        $data = $this->dashboardData($filters);
        $this->assertEquals(50, $data['totalOrderPrice']);
        $this->assertEquals(50, $data['totalOrderRemainingAmount']);
        $this->assertEquals(1, $data['pendingOrderCount']);
        $response = app(DashboardController::class)->index(new Request($filters + ['ajax' => 'dashboard']));
        $html = $response->getData(true)['html'];
        $this->assertStringContainsString('$50.00', $html);
        $this->assertStringNotContainsString('$900.00', $html);
        $this->assertStringNotContainsString('data-product-month', $html);
        $this->assertStringNotContainsString('timeline-container', $html);
        $this->assertStringNotContainsString(__('dashboard.products_count'), $html);
    }

    /** @dataProvider invalidDashboardDateRanges */
    public function test_custom_range_rejects_invalid_dates_before_querying(array $filters): void
    {
        $this->actingAs($this->allUser);
        $this->expectException(ValidationException::class);
        $this->dashboardData($filters);
    }

    public function invalidDashboardDateRanges(): array
    {
        return [
            [['from_date' => '2026-10-02', 'to_date' => '2026-10-01']],
            [['from_date' => '2026-02-30', 'to_date' => '2026-03-01']],
            [['from_date' => '10/01/2026', 'to_date' => '2026-10-01']],
            [['from_date' => '2026-10-01']],
            [['to_date' => '2026-10-01']],
            [['from_date' => ['2026-10-01'], 'to_date' => '2026-10-01']],
        ];
    }
}
