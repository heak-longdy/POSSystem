<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\OrderController;
use App\Http\Requests\Admin\OrderRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Shop;
use App\Models\StockOnHand;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class BonusStockDeductionTest extends TestCase
{
    public function test_bonus_item_stock_delta_flow(): void
    {
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);
        \Illuminate\Support\Facades\DB::purge();

        $schema = \Illuminate\Support\Facades\Schema::connection('sqlite');

        $schema->create('users', function ($table) {
            $table->id();
            $table->string('name')->default('Admin');
            $table->timestamps();
            $table->softDeletes();
        });

        $user = new \App\Models\User();
        $user->forceFill(['id' => 1, 'name' => 'Admin'])->save();
        Auth::login($user);

        $schema->create('shops', function ($table) {
            $table->id();
            $table->string('name')->default('Shop 1');
            $table->integer('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
        Shop::create(['id' => 1, 'name' => 'Shop 1', 'status' => 1]);

        $schema->create('customers', function ($table) {
            $table->id();
            $table->string('name')->default('Customer 1');
            $table->integer('status')->default(1);
            $table->string('payment_restriction')->default('N');
            $table->timestamps();
            $table->softDeletes();
        });
        Customer::create(['id' => 1, 'name' => 'Customer 1', 'status' => 1]);

        $schema->create('products', function ($table) {
            $table->id();
            $table->string('name')->default('Dasani 1.5L');
            $table->string('code')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('cost', 10, 2)->default(0);
            $table->integer('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
        Product::create(['id' => 4830, 'name' => 'Dasani 1.5L', 'status' => 1]);

        $schema->create('stock_on_hands', function ($table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('current_stock', 10, 2)->default(0);
            $table->string('remark')->nullable();
            $table->integer('status')->default(1);
            $table->unsignedBigInteger('request_by')->nullable();
            $table->string('request_by_type')->nullable();
            $table->timestamps();
        });

        $schema->create('stock_outs', function ($table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('shop_id');
            $table->unsignedBigInteger('to_id')->nullable();
            $table->decimal('qty', 10, 2)->default(0);
            $table->string('remark')->nullable();
            $table->string('type')->nullable();
            $table->integer('status')->default(1);
            $table->unsignedBigInteger('request_by')->nullable();
            $table->string('request_by_type')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $schema->create('stock_histories', function ($table) {
            $table->id();
            $table->unsignedBigInteger('transfer_id')->nullable();
            $table->unsignedBigInteger('stock_id')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->decimal('current_stock', 10, 2)->default(0);
            $table->decimal('stock_in', 10, 2)->default(0);
            $table->decimal('stock_out', 10, 2)->default(0);
            $table->unsignedBigInteger('shop_id');
            $table->unsignedBigInteger('to_id')->nullable();
            $table->decimal('qty', 10, 2)->default(0);
            $table->string('remark')->nullable();
            $table->string('status')->nullable();
            $table->string('type')->nullable();
            $table->string('transfer_type')->nullable();
            $table->unsignedBigInteger('request_by')->nullable();
            $table->string('request_by_type')->nullable();
            $table->timestamps();
        });

        $schema->create('orders', function ($table) {
            $table->id();
            $table->string('invoice_number')->nullable();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('shop_id');
            $table->dateTime('order_date')->nullable();
            $table->date('delivery_date')->nullable();
            $table->decimal('total_price', 10, 2)->default(0);
            $table->decimal('total_discount', 10, 2)->default(0);
            $table->decimal('total_commission', 10, 2)->default(0);
            $table->decimal('previous_balance', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('remaining_amount', 10, 2)->default(0);
            $table->string('pay_way')->default('Cash');
            $table->string('payment_status')->default('Pending');
            $table->dateTime('payment_date')->nullable();
            $table->integer('total_point')->default(0);
            $table->string('creation_request_key')->nullable();
            $table->string('creation_payload_hash')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $schema->create('order_details', function ($table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('type')->default('product');
            $table->boolean('is_bonus')->default(false);
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('service_id')->nullable();
            $table->decimal('qty', 10, 2)->default(1);
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('original_price', 10, 2)->default(0);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->string('pricing_method')->nullable();
            $table->decimal('product_discount', 10, 2)->default(0);
            $table->string('product_discount_type')->nullable();
            $table->decimal('service_discount', 10, 2)->default(0);
            $table->string('service_discount_type')->nullable();
            $table->decimal('service_commission', 10, 2)->default(0);
            $table->string('service_commission_type')->nullable();
            $table->decimal('product_commission', 10, 2)->default(0);
            $table->string('product_commission_type')->nullable();
            $table->integer('point')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        $schema->create('order_payments', function ($table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('payment_method')->default('Cash');
            $table->dateTime('payment_date')->nullable();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $schema->create('order_balance_transfers', function ($table) {
            $table->id();
            $table->unsignedBigInteger('previous_order_id');
            $table->unsignedBigInteger('new_order_id');
            $table->decimal('transferred_amount', 15, 2)->default(0);
            $table->string('original_payment_status')->default('Pending');
            $table->dateTime('transferred_at')->nullable();
            $table->unsignedBigInteger('transferred_by')->nullable();
            $table->string('transfer_status')->default('completed');
            $table->dateTime('reversed_at')->nullable();
            $table->unsignedBigInteger('reversed_by')->nullable();
            $table->string('reversal_reason')->nullable();
            $table->timestamps();
        });

        $schema->create('shop_products', function ($table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('price', 10, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        $schema->create('customer_points', function ($table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->integer('total_point')->default(0);
            $table->integer('total_receving_point')->default(0);
            $table->integer('used_point')->default(0);
            $table->integer('count_of_using_service')->default(0);
            $table->timestamps();
        });

        $stock = StockOnHand::create([
            'shop_id' => 1,
            'product_id' => 4830,
            'current_stock' => 381.00,
        ]);

        \App\Models\ShopProduct::create([
            'shop_id' => 1,
            'product_id' => 4830,
            'price' => 10.00,
        ]);

        $controller = new OrderController();

        // 1. Create order with regular item + bonus qty = 3
        $req1 = new OrderRequest();
        $req1->merge([
            'shop_id' => 1,
            'customer_id' => 1,
            'order_date' => '2026-11-15',
            'dataCarts' => json_encode([
                [
                    'product_id' => 4830,
                    'product_type' => 'product',
                    'product_qty' => 1,
                    'is_bonus' => false,
                    'itemData' => [
                        'price' => 10,
                        'originalPrice' => 10,
                        'unitPrice' => '10.00',
                        'pricingMethod' => 'manual',
                        'discount' => 0,
                        'total' => 10,
                    ]
                ]
            ]),
            'dataBonusCarts' => json_encode([
                [
                    'product_id' => 4830,
                    'product_type' => 'product',
                    'product_qty' => 3,
                    'is_bonus' => true,
                ]
            ]),
        ]);

        $res1 = $controller->Save($req1);
        $stock->refresh();
        // 381 - 1 (regular) - 3 (bonus) = 377
        $this->assertEquals(377.00, (float) $stock->current_stock);

        $order = Order::latest('id')->first();
        $this->assertNotNull($order);
        $this->assertEquals('Pending', $order->payment_status);

        // 2. Edit order: keep regular item at 1, change bonus qty from 3 to 3.5
        $req2 = new OrderRequest();
        $req2->merge([
            'id' => $order->id,
            'shop_id' => 1,
            'customer_id' => 1,
            'order_date' => '2026-11-15',
            'dataCarts' => json_encode([
                [
                    'id' => $order->orderDetails->firstWhere('is_bonus', false)->id,
                    'product_id' => 4830,
                    'product_type' => 'product',
                    'product_qty' => 1,
                    'is_bonus' => false,
                    'itemData' => [
                        'price' => 10,
                        'originalPrice' => 10,
                        'unitPrice' => '10.00',
                        'pricingMethod' => 'manual',
                        'discount' => 0,
                        'total' => 10,
                    ]
                ]
            ]),
            'dataBonusCarts' => json_encode([
                [
                    'product_id' => 4830,
                    'product_type' => 'product',
                    'product_qty' => 3.5,
                    'is_bonus' => true,
                ]
            ]),
        ]);

        $res2 = $controller->Save($req2, $order->id);
        $stock->refresh();
        // Old: 381 - 1 - 3 = 377.
        // New: 381 - 1 - 3.5 = 376.5.
        // Diff for bonus is 3.5 - 3 = 0.5 additional deduction from 377 = 376.5.
        $this->assertEquals(376.50, (float) $stock->current_stock);

        // 3. Edit order: decrease bonus quantity from 3.5 to 2
        $req3 = new OrderRequest();
        $req3->merge([
            'id' => $order->id,
            'shop_id' => 1,
            'customer_id' => 1,
            'order_date' => '2026-11-15',
            'dataCarts' => json_encode([
                [
                    'id' => $order->orderDetails()->where('is_bonus', false)->first()->id,
                    'product_id' => 4830,
                    'product_type' => 'product',
                    'product_qty' => 1,
                    'is_bonus' => false,
                    'itemData' => [
                        'price' => 10,
                        'originalPrice' => 10,
                        'unitPrice' => '10.00',
                        'pricingMethod' => 'manual',
                        'discount' => 0,
                        'total' => 10,
                    ]
                ]
            ]),
            'dataBonusCarts' => json_encode([
                [
                    'product_id' => 4830,
                    'product_type' => 'product',
                    'product_qty' => 2,
                    'is_bonus' => true,
                ]
            ]),
        ]);

        $res3 = $controller->Save($req3, $order->id);
        $stock->refresh();
        // Decrease bonus from 3.5 to 2: restores 1.5 back to stock
        // 376.5 + 1.5 = 378.00
        $this->assertEquals(378.00, (float) $stock->current_stock);

        // 4. Edit order: remove bonus item completely
        $req4 = new OrderRequest();
        $req4->merge([
            'id' => $order->id,
            'shop_id' => 1,
            'customer_id' => 1,
            'order_date' => '2026-11-15',
            'dataCarts' => json_encode([
                [
                    'id' => $order->orderDetails()->where('is_bonus', false)->first()->id,
                    'product_id' => 4830,
                    'product_type' => 'product',
                    'product_qty' => 1,
                    'is_bonus' => false,
                    'itemData' => [
                        'price' => 10,
                        'originalPrice' => 10,
                        'unitPrice' => '10.00',
                        'pricingMethod' => 'manual',
                        'discount' => 0,
                        'total' => 10,
                    ]
                ]
            ]),
            'dataBonusCarts' => json_encode([]),
        ]);

        $res4 = $controller->Save($req4, $order->id);
        $stock->refresh();
        // Remove bonus item (old qty was 2): restores 2 back to stock
        // 378 + 2 = 380.00 (initial 381 minus 1 regular item = 380)
        $this->assertEquals(380.00, (float) $stock->current_stock);

        // 5. Test exact user scenario: Bonus-only order starting with stock 378
        $stockBonusOnly = StockOnHand::create([
            'shop_id' => 1,
            'product_id' => 4831,
            'current_stock' => 378.00,
        ]);
        Product::create(['id' => 4831, 'name' => 'ដាសានី1,5លីត្រ(6+2)', 'status' => 1]);
        \App\Models\ShopProduct::create([
            'shop_id' => 1,
            'product_id' => 4831,
            'price' => 0.00,
        ]);

        $reqBonusCreate = new OrderRequest();
        $reqBonusCreate->merge([
            'shop_id' => 1,
            'customer_id' => 1,
            'order_date' => '2026-11-15',
            'dataCarts' => json_encode([]),
            'dataBonusCarts' => json_encode([
                [
                    'product_id' => 4831,
                    'product_type' => 'product',
                    'product_qty' => 3,
                    'is_bonus' => true,
                ]
            ]),
        ]);

        $resBonus = $controller->Save($reqBonusCreate);
        $stockBonusOnly->refresh();
        // Stock 378 - 3 = 375
        $this->assertEquals(375.00, (float) $stockBonusOnly->current_stock);

        $bonusOrder = Order::latest('id')->first();
        // Order with only bonus items must remain Pending and editable
        $this->assertEquals('Pending', $bonusOrder->payment_status);

        // Edit bonus order: change Free Quantity from 3 to 3.5
        // If current stock was 378 before edit (e.g. 375 + 3 available):
        // Set current stock to 378 as described in the user's scenario
        $stockBonusOnly->update(['current_stock' => 378.00]);

        $reqBonusEdit = new OrderRequest();
        $reqBonusEdit->merge([
            'id' => $bonusOrder->id,
            'shop_id' => 1,
            'customer_id' => 1,
            'order_date' => '2026-11-15',
            'dataCarts' => json_encode([]),
            'dataBonusCarts' => json_encode([
                [
                    'product_id' => 4831,
                    'product_type' => 'product',
                    'product_qty' => 3.5,
                    'is_bonus' => true,
                ]
            ]),
        ]);

        $controller->Save($reqBonusEdit, $bonusOrder->id);
        $stockBonusOnly->refresh();
        // Original bonus qty: 3, updated bonus qty: 3.5, diff = +0.5
        // Stock 378 - 0.5 = 377.50
        $this->assertEquals(377.50, (float) $stockBonusOnly->current_stock);
    }
}
