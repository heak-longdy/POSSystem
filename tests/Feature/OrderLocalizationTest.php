<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\OrderController;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderLocalizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $kmUser;
    protected User $enUser;
    protected Shop $shop;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);

        $this->kmUser = User::factory()->create([
            'role' => 'admin',
            'status' => 1,
            'language_preference' => 'km',
        ]);
        $this->kmUser->givePermissionTo(['order-view', 'order-create', 'order-update', 'order-delete']);

        $this->enUser = User::factory()->create([
            'role' => 'admin',
            'status' => 1,
            'language_preference' => 'en',
        ]);
        $this->enUser->givePermissionTo(['order-view', 'order-create', 'order-update', 'order-delete']);

        if (!\Illuminate\Support\Facades\Schema::hasTable('orders')) {
            \Illuminate\Support\Facades\Schema::create('orders', function ($table) {
                $table->id();
                $table->string('invoice_number')->nullable();
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->unsignedBigInteger('shop_id')->nullable();
                $table->unsignedBigInteger('barber_id')->nullable();
                $table->decimal('total_price', 10, 2)->default(0);
                $table->decimal('paid_amount', 10, 2)->default(0);
                $table->decimal('remaining_amount', 10, 2)->default(0);
                $table->string('payment_status')->default('Pending');
                $table->decimal('commission', 10, 2)->default(0);
                $table->decimal('discount', 10, 2)->default(0);
                $table->decimal('rate', 10, 2)->default(0);
                $table->timestamp('order_date')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }
        if (!\Illuminate\Support\Facades\Schema::hasTable('order_details')) {
            \Illuminate\Support\Facades\Schema::create('order_details', function ($table) {
                $table->id();
                $table->unsignedBigInteger('order_id')->nullable();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->unsignedBigInteger('service_id')->nullable();
                $table->string('type')->default('product');
                $table->decimal('price', 10, 2)->default(0);
                $table->integer('qty')->default(1);
                $table->softDeletes();
                $table->timestamps();
            });
        }

        $this->shop = Shop::create([
            'name' => 'TK Branch',
            'phone' => '012' . rand(100000, 999999),
            'status' => 1,
        ]);

        $this->customer = Customer::create([
            'name' => 'Sok Dara',
            'phone' => '099' . rand(100000, 999999),
            'status' => 1,
            'user' => $this->kmUser->id,
        ]);
    }

    /** @test */
    public function it_renders_order_list_page_in_khmer()
    {
        Order::create([
            'customer_id' => $this->customer->id,
            'shop_id' => $this->shop->id,
            'total_price' => 100.00,
            'paid_amount' => 0.00,
            'payment_status' => 'Pending',
            'order_date' => now(),
        ]);

        $this->actingAs($this->kmUser);

        $response = $this->get(route('admin-order-list', 'Pending'));

        $response->assertStatus(200);
        $response->assertSee('ការគ្រប់គ្រងការបញ្ជាទិញ');
        $response->assertSee('បង្កើតការបញ្ជាទិញ');
        $response->assertSee('រង់ចាំ');
        $response->assertSee('លេខកូដបញ្ជាទិញ');
        $response->assertSee('ហាង');
        $response->assertSee('កែប្រែ');
    }

    /** @test */
    public function it_renders_order_list_page_in_english()
    {
        Order::create([
            'customer_id' => $this->customer->id,
            'shop_id' => $this->shop->id,
            'total_price' => 100.00,
            'paid_amount' => 0.00,
            'payment_status' => 'Pending',
            'order_date' => now(),
        ]);

        $this->actingAs($this->enUser);

        $response = $this->get(route('admin-order-list', 'Pending'));

        $response->assertStatus(200);
        $response->assertSee('Order Management');
        $response->assertSee('Create Order');
        $response->assertSee('Pending');
        $response->assertSee('Order ID');
        $response->assertSee('Shop');
    }

    /** @test */
    public function it_renders_order_create_page_in_khmer()
    {
        $this->actingAs($this->kmUser);

        $response = $this->get(route('admin-order-create'));

        $response->assertStatus(200);
        $response->assertSee('បង្កើតការបញ្ជាទិញ');
        $response->assertSee('កាតាឡុកទំនិញ');
        $response->assertSee('ព័ត៌មានអតិថិជន និងហាង');
        $response->assertSee('សាច់ប្រាក់');
    }

    /** @test */
    public function it_renders_order_list_product_page_in_khmer()
    {
        $this->actingAs($this->kmUser);

        $response = $this->get(route('admin-order-list-product', 1));

        $response->assertStatus(200);
        $response->assertSee('ការកុម្ម៉ង់ផលិតផល');
        $response->assertSee('សេវាកម្ម');
        $response->assertSee('ផលិតផល');
        $response->assertSee('ហាងទាំងអស់');
    }

    /** @test */
    public function it_validates_order_request_with_localized_messages()
    {
        $this->actingAs($this->kmUser);

        $response = $this->postJson(route('admin-order-save'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'shop_id' => 'សូមជ្រើសរើសហាង',
            'customer_id' => 'សូមជ្រើសរើសអតិថិជន',
            'order_date' => 'សូមជ្រើសរើសកាលបរិច្ឆេទបញ្ជាទិញ',
            'dataCarts' => 'សូមជ្រើសរើសទំនិញយ៉ាងតិចមួយក្នុងកន្ត្រក',
        ]);
    }

    /** @test */
    public function it_returns_localized_order_status_labels()
    {
        app()->setLocale('km');
        $this->assertEquals('បានបង់ប្រាក់', OrderController::orderStatusLabel('Paid'));
        $this->assertEquals('បង់ប្រាក់ខ្លះ', OrderController::orderStatusLabel('Partial'));
        $this->assertEquals('បានបដិសេធ', OrderController::orderStatusLabel('Cancel'));
        $this->assertEquals('រង់ចាំ', OrderController::orderStatusLabel('Pending'));

        app()->setLocale('en');
        $this->assertEquals('Paid', OrderController::orderStatusLabel('Paid'));
        $this->assertEquals('Partial', OrderController::orderStatusLabel('Partial'));
        $this->assertEquals('Rejected', OrderController::orderStatusLabel('Cancel'));
        $this->assertEquals('Pending', OrderController::orderStatusLabel('Pending'));
    }

    /** @test */
    public function it_renders_order_detail_page_in_english_and_khmer()
    {
        $order = Order::create([
            'customer_id' => $this->customer->id,
            'shop_id' => $this->shop->id,
            'total_price' => 150.00,
            'paid_amount' => 50.00,
            'payment_status' => 'Partial',
            'order_date' => now(),
            'invoice_number' => 'ORD-TEST-001',
        ]);

        $this->actingAs($this->enUser);
        $enList = $this->get(route('admin-order-list', 'Pending'));
        $enList->assertStatus(200);
        $enList->assertSee('View Details');

        $enResponse = $this->get(route('admin-order-detail', $order->id));
        $enResponse->assertStatus(200);
        $enResponse->assertSee('Order Details');
        $enResponse->assertSee('ORD-TEST-001');
        $enResponse->assertSee('Total Bill');
        $enResponse->assertSee('Balance Due');

        $this->actingAs($this->kmUser);
        $kmList = $this->get(route('admin-order-list', 'Pending'));
        $kmList->assertStatus(200);
        $kmList->assertSee('មើលព័ត៌មានលម្អិត');

        $kmResponse = $this->get(route('admin-order-detail', $order->id));
        $kmResponse->assertStatus(200);
        $kmResponse->assertSee('ព័ត៌មានលម្អិតនៃការបញ្ជាទិញ');
        $kmResponse->assertSee('ORD-TEST-001');
        $kmResponse->assertSee('តម្លៃសរុប');
        $kmResponse->assertSee('ប្រាក់នៅខ្វះ');
    }

    /** @test */
    public function it_safely_handles_dynamic_foreign_keys_and_date_attributes()
    {
        $order = Order::create([
            'customer_id' => $this->customer->id,
            'shop_id' => $this->shop->id,
            'total_price' => 100.00,
            'paid_amount' => 50.00,
            'payment_status' => 'Partial',
            'order_date' => now(),
            'invoice_number' => 'ORD-DYNAMIC-001',
        ]);

        $detail = \App\Models\OrderDetail::create([
            'order_id' => $order->id,
            'price' => 50.00,
            'qty' => 2,
            'type' => 'product',
        ]);

        $this->assertEquals($order->id, $detail->order_id);

        $payment = \App\Models\OrderPayment::create([
            'order_id' => $order->id,
            'amount' => 50.00,
            'payment_method' => 'Cash',
            'note' => 'Test payment',
        ]);

        $this->assertEquals($order->id, $payment->order_id);
        $this->assertEquals(50.00, $order->fresh()->remaining_amount);
    }
}
