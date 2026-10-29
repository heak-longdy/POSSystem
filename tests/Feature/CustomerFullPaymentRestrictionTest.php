<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\Shop;
use App\Models\User;
use App\Services\OrderBalanceTransferService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomerFullPaymentRestrictionTest extends TestCase
{
    protected User $user;
    protected Shop $shop;
    protected Customer $unrestrictedCustomer;
    protected Customer $restrictedCustomer;

    protected function setUp(): void
    {
        parent::setUp();

        require_once database_path('migrations/2026_08_02_231335_create_permission_tables.php');
        (new \CreatePermissionTables())->up();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('role')->nullable();
            $table->string('data_access')->default('all');
            $table->boolean('status')->default(true);
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
            $table->string('user')->default('1');
            $table->integer('status')->default(1);
            $table->string('payment_restriction', 1)->default('N');
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

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('shop_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');
            $table->unsignedBigInteger('service_id');
            $table->double('price')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('customer_points', function (Blueprint $table) {
            $table->id();
            foreach (['shop_id', 'customer_id', 'brand_id', 'total_point', 'total_receving_point', 'used_point', 'count_of_using_service'] as $column) {
                $table->integer($column)->nullable();
            }
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('services')->insert(['id' => 1, 'name' => 'Haircut service']);
        DB::table('shop_services')->insert(['id' => 1, 'shop_id' => 1, 'service_id' => 1, 'price' => 50.0]);

        $this->user = User::create([
            'id' => 1,
            'name' => 'Admin Test',
            'role' => 'super_admin',
            'data_access' => 'all',
        ]);
        $this->shop = Shop::create(['id' => 1, 'name' => 'Main POS Shop', 'status' => 1]);

        $this->unrestrictedCustomer = Customer::create([
            'id' => 1,
            'name' => 'Unrestricted Customer',
            'phone' => '012111111',
            'user' => '1',
            'status' => 1,
            'payment_restriction' => 'N',
        ]);

        $this->restrictedCustomer = Customer::create([
            'id' => 2,
            'name' => 'Restricted Customer',
            'phone' => '012222222',
            'user' => '1',
            'status' => 1,
            'payment_restriction' => 'Y',
        ]);

        $this->actingAs($this->user);
    }

    private function createPayload(int $customerId, $paymentAmount, float $price = 20.0): array
    {
        return [
            'shop_id' => $this->shop->id,
            'customer_id' => $customerId,
            'order_date' => '2026-10-28',
            'subTotal' => $price,
            'partial_payment_amount' => $paymentAmount,
            'partial_payment_note' => 'Payment test',
            'pay_way' => 'Cash',
            'dataCarts' => json_encode([[
                'product_id' => 1,
                'product_type' => 'service',
                'product_qty' => 1,
                'itemData' => ['price' => $price],
            ]]),
        ];
    }

    public function test_customer_model_helper_method(): void
    {
        $this->assertFalse($this->unrestrictedCustomer->isFullPaymentRestricted());
        $this->assertTrue($this->restrictedCustomer->isFullPaymentRestricted());

        $defaultCustomer = new Customer();
        $this->assertFalse($defaultCustomer->isFullPaymentRestricted());

        $lowerCustomer = new Customer(['payment_restriction' => 'y']);
        $this->assertTrue($lowerCustomer->isFullPaymentRestricted());
    }

    public function test_customer_request_validates_payment_restriction(): void
    {
        $validData = [
            'name' => 'Valid Customer',
            'phone' => '012345678',
            'status' => 1,
            'payment_restriction' => 'Y',
        ];

        $request = new \App\Http\Requests\Admin\CustomerRequest();
        $validator = \Illuminate\Support\Facades\Validator::make($validData, $request->rules());
        $this->assertTrue($validator->passes());

        $invalidData = array_merge($validData, ['payment_restriction' => 'INVALID']);
        $invalidValidator = \Illuminate\Support\Facades\Validator::make($invalidData, $request->rules());
        $this->assertTrue($invalidValidator->fails());
        $this->assertArrayHasKey('payment_restriction', $invalidValidator->errors()->toArray());
    }

    public function test_preview_previous_balance_returns_restriction_flag(): void
    {
        $responseUnrestricted = $this->getJson('/admin/order/preview-previous-balance?customer_id=' . $this->unrestrictedCustomer->id)
            ->assertOk();
        $this->assertSame('N', $responseUnrestricted->json('data.payment_restriction'));
        $this->assertFalse($responseUnrestricted->json('data.is_restricted'));

        $responseRestricted = $this->getJson('/admin/order/preview-previous-balance?customer_id=' . $this->restrictedCustomer->id)
            ->assertOk();
        $this->assertSame('Y', $responseRestricted->json('data.payment_restriction'));
        $this->assertTrue($responseRestricted->json('data.is_restricted'));
    }

    public function test_unrestricted_customer_can_create_pending_partial_and_paid_orders(): void
    {
        $c1 = Customer::create(['name' => 'C1', 'phone' => '111', 'user' => '1', 'status' => 1, 'payment_restriction' => 'N']);
        $c2 = Customer::create(['name' => 'C2', 'phone' => '222', 'user' => '1', 'status' => 1, 'payment_restriction' => 'N']);
        $c3 = Customer::create(['name' => 'C3', 'phone' => '333', 'user' => '1', 'status' => 1, 'payment_restriction' => 'N']);

        // 1. Pending (amount = 0)
        $respPending = $this->postJson('/admin/order/save', $this->createPayload($c1->id, 0, 50.0))
            ->assertOk()->assertJsonPath('payment_status', 'Pending');
        $this->assertSame('Pending', Order::findOrFail($respPending->json('id'))->payment_status);

        // 2. Partial (amount = 20 of 50)
        $respPartial = $this->postJson('/admin/order/save', $this->createPayload($c2->id, 20.0, 50.0))
            ->assertOk()->assertJsonPath('payment_status', 'Partial');
        $this->assertSame('Partial', Order::findOrFail($respPartial->json('id'))->payment_status);

        // 3. Paid (amount = 50 of 50)
        $respPaid = $this->postJson('/admin/order/save', $this->createPayload($c3->id, 50.0, 50.0))
            ->assertOk()->assertJsonPath('payment_status', 'Paid');
        $this->assertSame('Paid', Order::findOrFail($respPaid->json('id'))->payment_status);
    }

    public function test_restricted_customer_is_rejected_on_zero_or_missing_payment(): void
    {
        // Zero payment
        $response = $this->postJson('/admin/order/save', $this->createPayload($this->restrictedCustomer->id, 0, 50.0))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('partial_payment_amount');

        $this->assertSame(__('order.validation.full_payment_required'), $response->json('errors.partial_payment_amount.0'));

        // Null / empty payment
        $responseNull = $this->postJson('/admin/order/save', $this->createPayload($this->restrictedCustomer->id, null, 50.0))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('partial_payment_amount');

        $this->assertSame(__('order.validation.full_payment_required'), $responseNull->json('errors.partial_payment_amount.0'));
    }

    public function test_restricted_customer_is_rejected_on_partial_payment(): void
    {
        // Partial payment (20 of 50)
        $response = $this->postJson('/admin/order/save', $this->createPayload($this->restrictedCustomer->id, 20.0, 50.0))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('partial_payment_amount');

        $this->assertSame(__('order.validation.full_payment_required'), $response->json('errors.partial_payment_amount.0'));
    }

    public function test_restricted_customer_succeeds_on_exact_full_payment(): void
    {
        $response = $this->postJson('/admin/order/save', $this->createPayload($this->restrictedCustomer->id, 50.0, 50.0))
            ->assertOk()
            ->assertJsonPath('payment_status', 'Paid');

        $order = Order::findOrFail($response->json('id'));
        $this->assertSame(50.0, $order->total_price);
        $this->assertSame(50.0, $order->paid_amount);
        $this->assertSame('Paid', $order->payment_status);
    }

    public function test_restricted_customer_with_previous_balance_must_pay_total_balance_due(): void
    {
        // 1. Create a previous unpaid order of $30 (created while customer was unrestricted or existing)
        $prevOrder = Order::create([
            'customer_id' => $this->restrictedCustomer->id,
            'shop_id' => $this->shop->id,
            'total_price' => 30.0,
            'previous_balance' => 0.0,
            'paid_amount' => 0.0,
            'payment_status' => 'Pending',
            'order_date' => '2026-10-20',
        ]);

        // 2. Restricted customer creates new order of $50. Total Balance Due = $30 (prev) + $50 (new) = $80.
        // Attempting to pay only the new order ($50) must be rejected
        $respUnderpaid = $this->postJson('/admin/order/save', $this->createPayload($this->restrictedCustomer->id, 50.0, 50.0))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('partial_payment_amount');

        $this->assertSame(__('order.validation.full_payment_required'), $respUnderpaid->json('errors.partial_payment_amount.0'));

        // Attempting to pay the full $80.00 succeeds
        $respFull = $this->postJson('/admin/order/save', $this->createPayload($this->restrictedCustomer->id, 80.0, 50.0))
            ->assertOk()
            ->assertJsonPath('payment_status', 'Paid');

        $newOrder = Order::findOrFail($respFull->json('id'));
        $this->assertSame(50.0, $newOrder->total_price);
        $this->assertSame(30.0, $newOrder->previous_balance);
        $this->assertSame(80.0, $newOrder->paid_amount);
        $this->assertSame('Paid', $newOrder->payment_status);

        // Previous order should have transferred its balance
        $this->assertSame('Paid', $prevOrder->fresh()->payment_status);
    }

    public function test_restricted_customer_with_zero_dollar_total_succeeds(): void
    {
        $freshRestricted = Customer::create(['name' => 'Zero Due Customer', 'phone' => '000', 'user' => '1', 'status' => 1, 'payment_restriction' => 'Y']);
        $response = $this->postJson('/admin/order/save', $this->createPayload($freshRestricted->id, 0, 0.0))
            ->assertOk()
            ->assertJsonPath('payment_status', 'Pending');

        $order = Order::findOrFail($response->json('id'));
        $this->assertSame(0.0, $order->total_price);
        $this->assertSame('Pending', $order->payment_status);
    }
}
