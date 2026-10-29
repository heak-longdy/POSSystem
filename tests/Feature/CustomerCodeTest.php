<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomerCodeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        require_once database_path('migrations/2026_08_02_231335_create_permission_tables.php');
        (new \CreatePermissionTables())->up();

        // Isolated SQLite test schema
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable()->unique();
            $table->string('name')->nullable();
            $table->integer('ordering')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('profile')->nullable();
            $table->string('password')->nullable();
            $table->integer('total_point')->default(0);
            $table->string('user')->default('admin');
            $table->string('payment_restriction', 10)->default('N');
            $table->integer('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id')->nullable();
            $table->integer('shop_id')->nullable();
            $table->dateTime('order_date')->nullable();
            $table->dateTime('delivery_date')->nullable();
            $table->string('payment_status')->default('Pending');
            $table->string('invoice_number')->nullable();
            $table->double('total_price')->default(0);
            $table->double('paid_amount')->default(0);
            $table->string('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id')->nullable();
            $table->string('type')->default('product');
            $table->integer('item_id')->nullable();
            $table->integer('qty')->default(1);
            $table->double('price')->default(0);
            $table->double('product_discount')->default(0);
            $table->double('service_discount')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('customers')->insert([
            [
                'id' => 1,
                'code' => 'CUST-001',
                'name' => 'Heak Longdy',
                'phone' => '012787802',
                'address' => 'Phnom Penh',
                'user' => '1',
                'payment_restriction' => 'N',
                'status' => 1,
                'created_at' => now(),
            ],
            [
                'id' => 15,
                'code' => 'CUST-015',
                'name' => 'Sok Dara',
                'phone' => '012888999',
                'address' => 'Siem Reap',
                'user' => '1',
                'payment_restriction' => 'Y',
                'status' => 1,
                'created_at' => now(),
            ],
        ]);

        DB::table('shops')->insert([
            'id' => 1,
            'name' => 'Main Depot',
            'phone' => '092989928',
            'address' => 'Tbong Khmum',
            'city' => 'Tbong Khmum',
            'created_at' => now(),
        ]);

        DB::table('orders')->insert([
            [
                'id' => 101,
                'customer_id' => 15,
                'shop_id' => 1,
                'order_date' => now(),
                'payment_status' => 'Pending',
                'invoice_number' => 'INV-00101',
                'total_price' => 120.00,
                'created_at' => now(),
            ],
        ]);

        $user = new User(['name' => 'Admin User', 'role' => 'super_admin', 'language_preference' => 'en']);
        $user->id = 1;
        $this->actingAs($user);
    }

    public function test_customer_form_contains_code_input_and_saves_user_entered_code(): void
    {
        // 1. Create form contains code input
        $createView = $this->get(route('admin-customer-create'))->assertOk();
        $this->assertStringContainsString('name="code"', $createView->getContent());

        // 2. Save new customer with custom code
        $postData = [
            'code' => 'VIP-999',
            'name' => 'Vip Customer',
            'phone' => '012999888',
            'address' => 'Tuol Kork',
            'status' => 1,
            'payment_restriction' => 'N',
        ];

        $response = $this->post(route('admin-customer-save'), $postData);
        $response->assertRedirect(route('admin-customer-list', 1));

        $saved = Customer::where('phone', '012999888')->first();
        $this->assertNotNull($saved);
        $this->assertEquals('VIP-999', $saved->code);

        // 3. Edit form contains saved custom code
        $editView = $this->get(route('admin-customer-edit', $saved->id))->assertOk();
        $this->assertStringContainsString('value="VIP-999"', $editView->getContent());

        // 4. Duplicate code validation fails
        $dupData = [
            'code' => 'VIP-999',
            'name' => 'Another Customer',
            'phone' => '012111222',
            'status' => 1,
        ];
        $this->post(route('admin-customer-save'), $dupData)->assertSessionHasErrors(['code']);
    }

    public function test_select_customer_endpoint_matches_by_custom_code_and_id_and_name_and_phone(): void
    {
        // 1. Search by custom entered code
        $resCode = $this->getJson(route('admin-select-customer', ['search' => 'CUST-015']))->assertOk();
        $data = $resCode->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals(15, $data[0]['id']);
        $this->assertEquals('CUST-015', $data[0]['code']);

        // 2. Search by numeric ID
        $resId = $this->getJson(route('admin-select-customer', ['search' => '15']))->assertOk();
        $this->assertCount(1, $resId->json('data'));
        $this->assertEquals(15, $resId->json('data.0.id'));

        // 3. Search by name
        $resName = $this->getJson(route('admin-select-customer', ['search' => 'Longdy']))->assertOk();
        $this->assertCount(1, $resName->json('data'));
        $this->assertEquals(1, $resName->json('data.0.id'));

        // 4. Search by phone
        $resPhone = $this->getJson(route('admin-select-customer', ['search' => '012787802']))->assertOk();
        $this->assertCount(1, $resPhone->json('data'));
        $this->assertEquals(1, $resPhone->json('data.0.id'));
    }

    public function test_customer_controller_search_filters_by_custom_code(): void
    {
        $res = $this->getJson(route('admin-customer-report', ['status' => 1, 'search' => 'CUST-015']))->assertOk();
        $rows = $res->json('rows');
        $this->assertCount(1, $rows);
        $this->assertEquals(15, $rows[0]['id']);
        $this->assertEquals('CUST-015', $rows[0]['code']);
    }

    public function test_customer_validation_error_rendering(): void
    {
        $response = $this->from(route('admin-customer-create'))
            ->post(route('admin-customer-save'), []);

        $response->assertRedirect(route('admin-customer-create'));
        $response->assertSessionHasErrors(['name', 'phone']);

        $follow = $this->followRedirects($response);
        $content = $follow->getContent();
        $this->assertStringContainsString(__('customer.validation.name_required'), $content);
        $this->assertStringContainsString(__('customer.validation.phone_required'), $content);
    }

    public function test_customer_code_can_be_empty_and_multiple_customers_allowed_without_code(): void
    {
        // Customer 1 with blank code
        $res1 = $this->post(route('admin-customer-save'), [
            'code' => '',
            'name' => 'No Code Customer 1',
            'phone' => '012111001',
            'status' => 1,
        ]);
        $res1->assertRedirect(route('admin-customer-list', 1));

        // Customer 2 with null/blank code
        $res2 = $this->post(route('admin-customer-save'), [
            'code' => null,
            'name' => 'No Code Customer 2',
            'phone' => '012111002',
            'status' => 1,
        ]);
        $res2->assertRedirect(route('admin-customer-list', 1));

        $cust1 = Customer::where('phone', '012111001')->first();
        $cust2 = Customer::where('phone', '012111002')->first();

        $this->assertNotNull($cust1);
        $this->assertNotNull($cust2);
        $this->assertNull($cust1->code);
        $this->assertNull($cust2->code);
    }

    public function test_customer_code_whitespace_is_trimmed(): void
    {
        $res = $this->post(route('admin-customer-save'), [
            'code' => '   TRIMMED-CODE-100   ',
            'name' => 'Whitespace Test',
            'phone' => '012555666',
            'status' => 1,
        ]);
        $res->assertRedirect(route('admin-customer-list', 1));

        $saved = Customer::where('phone', '012555666')->first();
        $this->assertNotNull($saved);
        $this->assertEquals('TRIMMED-CODE-100', $saved->code);
    }

    public function test_customer_update_allows_keeping_own_code_and_blocks_other_customer_code(): void
    {
        // 1. Update customer 15 keeping its own code CUST-015
        $resOwn = $this->post(route('admin-customer-save', 15), [
            'code' => 'CUST-015',
            'name' => 'Sok Dara Updated',
            'phone' => '012888999',
            'status' => 1,
        ]);
        $resOwn->assertSessionHasNoErrors();
        $resOwn->assertRedirect(route('admin-customer-list', 1));

        $updated = Customer::find(15);
        $this->assertEquals('Sok Dara Updated', $updated->name);

        // 2. Update customer 15 attempting to take customer 1's code CUST-001
        $resDup = $this->post(route('admin-customer-save', 15), [
            'code' => 'CUST-001',
            'name' => 'Sok Dara Hijack',
            'phone' => '012888999',
            'status' => 1,
        ]);
        $resDup->assertSessionHasErrors(['code']);
    }

    public function test_customer_csv_export_includes_code_column_and_values(): void
    {
        $response = $this->get(route('admin-customer-export', ['status' => 1]));
        $response->assertOk();

        $content = $response->streamedContent();
        $this->assertStringContainsString(__('customer.table.code'), $content);
        $this->assertStringContainsString('CUST-001', $content);
        $this->assertStringContainsString('CUST-015', $content);
    }
}
