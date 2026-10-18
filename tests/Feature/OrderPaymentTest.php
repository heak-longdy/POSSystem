<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\OrderController;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrderPaymentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // TestCase selects SQLite :memory:; never migrate or pay a real order.
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        require_once database_path('migrations/2026_08_02_231335_create_permission_tables.php');
        (new \CreatePermissionTables())->up();

        foreach (['users', 'customers', 'shops', 'services', 'barbers', 'products'] as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->boolean('status')->default(true);
                $table->softDeletes();
            });
            DB::table($name)->insert(['id' => 1, 'name' => 'Payment test']);
        }

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id')->nullable();
            $table->integer('shop_id')->nullable();
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
            $table->string('type');
            foreach (['product', 'service'] as $type) {
                $table->double($type . '_discount')->default(0);
                $table->string($type . '_discount_type')->nullable();
                $table->double($type . '_commission')->default(0);
                $table->string($type . '_commission_type')->nullable();
            }
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('shop_services', function (Blueprint $table) {
            $table->id();
            $table->integer('shop_id');
            $table->integer('service_id');
            $table->double('price');
        });
        DB::table('shop_services')->insert(['shop_id' => 1, 'service_id' => 1, 'price' => 19.65]);
        Schema::create('customer_points', function (Blueprint $table) {
            $table->id();
            foreach (['shop_id', 'customer_id', 'brand_id', 'total_point', 'total_receving_point', 'used_point', 'count_of_using_service'] as $column) {
                $table->integer($column)->nullable();
            }
            $table->timestamps();
            $table->softDeletes();
        });

        $user = new User(['name' => 'Payment admin', 'role' => 'super_admin', 'language_preference' => 'en']);
        $user->id = 1;
        $this->actingAs($user);
    }

    private function order(float $total = 19.65, array $payments = [10]): Order
    {
        $paid = array_sum($payments);
        $order = Order::create([
            'invoice_number' => 'NO-0009',
            'total_price' => $total,
            'paid_amount' => $paid,
            'payment_status' => $paid >= $total ? 'Paid' : ($paid > 0 ? 'Partial' : 'Pending'),
            'pay_way' => 'Cash',
        ]);
        foreach ($payments as $amount) {
            OrderPayment::create(['order_id' => $order->id, 'amount' => $amount, 'payment_method' => 'Cash']);
        }

        return $order;
    }

    private function creationPayload($payment, float $total = 19.65): array
    {
        return [
            'shop_id' => 1,
            'customer_id' => 1,
            'order_date' => '2026-10-15',
            'subTotal' => $total,
            'partial_payment_amount' => $payment,
            'partial_payment_note' => 'Deposit',
            'pay_way' => 'ABA',
            'dataCarts' => json_encode([[
                'product_id' => 1,
                'product_type' => 'service',
                'product_qty' => 1,
                'itemData' => ['price' => $total],
            ]]),
        ];
    }

    private function assertPrintedInvoiceSummary($response, array $expected): void
    {
        $document = new \DOMDocument();
        @$document->loadHTML('<?xml encoding="UTF-8">' . $response->getContent());
        $xpath = new \DOMXPath($document);

        foreach (['en', 'km'] as $locale) {
            $cells = $xpath->query('//div[@id="posInvoicePrintArea_' . $locale . '"]//tfoot//td[contains(@class, "inv-foot-calc-value")]');
            $values = array_map(fn ($cell) => trim($cell->textContent), iterator_to_array($cells));
            $this->assertSame($expected, $values, 'Printed invoice summary for ' . $locale);
        }
    }

    public function test_printed_previous_balance_sums_only_customer_pending_and_partial_unpaid_amounts(): void
    {
        $this->order(100, [])->update(['customer_id' => 1]);
        $this->order(80, [20, 15])->update(['customer_id' => 1, 'shop_id' => 2]);
        $this->order(19.65, [10])->update(['customer_id' => 1]);
        // Even a Pending order with a saved payment contributes only its unpaid amount.
        $this->order(30, [5])->update(['customer_id' => 1, 'payment_status' => 'Pending']);

        $this->order(500, [500])->update(['customer_id' => 1]);
        $this->order(500, [])->update(['customer_id' => 1, 'payment_status' => 'Paid']);
        $this->order(500, [])->update(['customer_id' => 1, 'payment_status' => 'Cancel']);
        $deleted = $this->order(500, []);
        $deleted->update(['customer_id' => 1]);
        $deleted->delete();
        $this->order(500, [])->update(['customer_id' => 2]);
        $this->order(500, []);

        $current = $this->order(200, [40]);
        $current->update(['customer_id' => 1]);
        // Reprinting an older invoice includes the customer's other current debts too.
        $this->order(500, [])->update(['customer_id' => 1]);
        $response = $this->get('/admin/order/detail/' . $current->id)->assertOk();
        $this->assertPrintedInvoiceSummary($response, ['679.65', '200.00', '40.00', '160.00']);

        $current->update(['paid_amount' => 0, 'payment_status' => 'Pending']);
        $response = $this->get('/admin/order/detail/' . $current->id)->assertOk();
        $this->assertPrintedInvoiceSummary($response, ['679.65', '200.00', '', '200.00']);
    }

    /** @dataProvider invoiceDetailPages */
    public function test_no_0014_prints_no_0015_unpaid_balance_from_both_detail_pages(string $page): void
    {
        DB::table('orders')->insert([
            ['id' => 13, 'invoice_number' => 'NO-0013', 'customer_id' => 1, 'total_price' => 41.28, 'paid_amount' => 41.28, 'payment_status' => 'Paid'],
            ['id' => 14, 'invoice_number' => 'NO-0014', 'customer_id' => 1, 'total_price' => 29.10, 'paid_amount' => 0, 'payment_status' => 'Pending'],
            ['id' => 15, 'invoice_number' => 'NO-0015', 'customer_id' => 1, 'total_price' => 62.77, 'paid_amount' => 10, 'payment_status' => 'Partial'],
        ]);

        $this->assertPrintedInvoiceSummary($this->get('/admin/' . $page . '/detail/14')->assertOk(), ['52.77', '29.10', '', '29.10']);
        $this->assertPrintedInvoiceSummary($this->get('/admin/' . $page . '/detail/15')->assertOk(), ['29.10', '62.77', '10.00', '52.77']);
    }

    public static function invoiceDetailPages(): array
    {
        return [['order'], ['remaining-amount']];
    }

    public function test_printed_previous_balance_refreshes_after_prior_order_payments_change(): void
    {
        $previous = $this->order(19.65, []);
        $previous->update(['customer_id' => 1]);
        $current = $this->order(50, []);
        $current->update(['customer_id' => 1]);
        $url = '/admin/order/detail/' . $current->id;
        $this->assertPrintedInvoiceSummary($this->get($url)->assertOk(), ['19.65', '50.00', '', '50.00']);

        $this->postJson('/admin/order/add-payment/' . $previous->id, ['amount' => 10])
            ->assertOk()->assertJsonPath('payment_status', 'Partial');
        $this->assertPrintedInvoiceSummary($this->get($url)->assertOk(), ['9.65', '50.00', '', '50.00']);

        $this->postJson('/admin/order/add-payment/' . $previous->id, ['amount' => 9.65])
            ->assertOk()->assertJsonPath('payment_status', 'Paid');
        $this->assertPrintedInvoiceSummary($this->get($url)->assertOk(), ['0.00', '50.00', '', '50.00']);

        $this->deleteJson('/admin/order/delete-payment/' . $previous->payments()->first()->id)->assertOk();
        $this->assertPrintedInvoiceSummary($this->get($url)->assertOk(), ['10.00', '50.00', '', '50.00']);
    }

    public function test_printed_previous_balance_preserves_cents_and_does_not_offset_other_debts_with_overpayments(): void
    {
        $this->order(0.30, [0.10])->update(['customer_id' => 1]);
        $this->order(0.80, [0.10, 0.70])->update(['customer_id' => 1, 'payment_status' => 'Partial']);
        $this->order(100, [110])->update(['customer_id' => 1, 'payment_status' => 'Partial']);
        $current = $this->order(50, []);
        $current->update(['customer_id' => 1]);

        $response = $this->get('/admin/order/detail/' . $current->id)->assertOk();
        $this->assertPrintedInvoiceSummary($response, ['0.20', '50.00', '', '50.00']);
    }

    public function test_printed_previous_balance_is_zero_for_walk_ins_and_customers_without_prior_debt(): void
    {
        $this->order(500, []);
        $this->order(500, [])->update(['customer_id' => 2]);
        $current = $this->order(50, []);
        $url = '/admin/order/detail/' . $current->id;
        $this->assertPrintedInvoiceSummary($this->get($url)->assertOk(), ['0.00', '50.00', '', '50.00']);

        $current->update(['customer_id' => 1]);
        $this->assertPrintedInvoiceSummary($this->get($url)->assertOk(), ['0.00', '50.00', '', '50.00']);
    }

    public function test_exact_displayed_balance_settles_no_0009_and_preserves_payment_details(): void
    {
        $order = $this->order();
        $this->getJson('/admin/order/payment-details/' . $order->id)
            ->assertOk()->assertJsonPath('remaining_amount', 9.65);

        $this->postJson('/admin/order/add-payment/' . $order->id, [
            'amount' => '9.65', 'payment_method' => 'ABA',
            'payment_date' => '2026-10-15 15:30:00', 'note' => 'Final payment',
        ])->assertOk()->assertJsonPath('payment_status', 'Paid')
            ->assertJsonPath('paid_amount', 19.65)->assertJsonPath('remaining_amount', 0);

        $payment = $order->payments()->get()->last();
        $this->assertSame(9.65, $payment->amount);
        $this->assertSame('ABA', $payment->payment_method);
        $this->assertSame('2026-10-15 15:30:00', $payment->payment_date->format('Y-m-d H:i:s'));
        $this->assertSame('Final payment', $payment->note);
        $this->assertEquals(1, $payment->created_by);
        $this->assertCount(2, $order->payments);
        $this->assertNotNull($order->fresh()->payment_date);
    }

    public function test_one_cent_overpayment_does_not_change_the_order_or_ledger(): void
    {
        $order = $this->order();
        $this->postJson('/admin/order/add-payment/' . $order->id, ['amount' => 9.66])
            ->assertUnprocessable()->assertJsonValidationErrors('amount');
        $this->assertSame(10.0, $order->fresh()->paid_amount);
        $this->assertSame('Partial', $order->fresh()->payment_status);
        $this->assertSame(1, $order->payments()->count());
    }

    public function test_partial_payment_then_last_cent_settles_without_residual_balance(): void
    {
        $order = $this->order();
        $this->postJson('/admin/order/add-payment/' . $order->id, ['amount' => 9.64])
            ->assertOk()->assertJsonPath('payment_status', 'Partial')->assertJsonPath('remaining_amount', 0.01);
        $this->postJson('/admin/order/add-payment/' . $order->id, ['amount' => 0.01])
            ->assertOk()->assertJsonPath('payment_status', 'Paid')->assertJsonPath('remaining_amount', 0);
    }

    public function test_multiple_installments_with_float_sum_noise_are_fully_paid(): void
    {
        $order = $this->order(0.8, [0.1, 0.7]);
        $payment = $order->payments()->first();
        $this->putJson('/admin/order/update-payment/' . $payment->id, ['amount' => 0.1])
            ->assertOk()->assertJsonPath('paid_amount', 0.8)
            ->assertJsonPath('payment_status', 'Paid')->assertJsonPath('remaining_amount', 0);
    }

    public function test_payment_edit_uses_available_cents_and_rejects_overpayment(): void
    {
        $order = $this->order(19.65, [10, 5]);
        $payment = $order->payments()->get()->last();
        $this->putJson('/admin/order/update-payment/' . $payment->id, ['amount' => 9.66])
            ->assertUnprocessable()->assertJsonValidationErrors('amount');
        $this->assertSame(5.0, $payment->fresh()->amount);
        $this->putJson('/admin/order/update-payment/' . $payment->id, ['amount' => 9.65])
            ->assertOk()->assertJsonPath('payment_status', 'Paid')->assertJsonPath('remaining_amount', 0);
    }

    public function test_deleted_payments_are_excluded_and_status_returns_to_partial_then_pending(): void
    {
        $order = $this->order(19.65, [10, 9.65]);
        $payments = $order->payments()->get();
        $this->deleteJson('/admin/order/delete-payment/' . $payments->last()->id)
            ->assertOk()->assertJsonPath('payment_status', 'Partial')->assertJsonPath('remaining_amount', 9.65);
        $this->assertNull($order->fresh()->payment_date);
        $this->deleteJson('/admin/order/delete-payment/' . $payments->first()->id)
            ->assertOk()->assertJsonPath('payment_status', 'Pending')->assertJsonPath('remaining_amount', 19.65);
        $this->assertSame(0, $order->payments()->count());
        $this->assertSame(2, OrderPayment::withTrashed()->where('order_id', $order->id)->count());
    }

    public function test_mark_paid_records_the_exact_cent_balance_only_once(): void
    {
        $order = $this->order();
        foreach ([1, 2] as $attempt) {
            $this->postJson('/admin/order/update-payment-status/' . $order->id, ['payment_status' => 'Paid'])
                ->assertOk()->assertJsonPath('payment_status', 'Paid')->assertJsonPath('remaining_amount', 0);
        }
        $this->assertSame(2, $order->payments()->count());
        $this->assertSame(9.65, $order->payments()->get()->last()->amount);
    }

    public function test_paid_and_cancelled_orders_still_reject_additional_payments(): void
    {
        foreach (['Paid', 'Cancel'] as $status) {
            $order = $this->order();
            $order->update(['payment_status' => $status]);
            $this->postJson('/admin/order/add-payment/' . $order->id, ['amount' => 9.65])
                ->assertUnprocessable()->assertJsonValidationErrors('payment_status');
            $this->assertSame(1, $order->payments()->count());
        }
    }

    public function test_invalid_amounts_cannot_record_a_hidden_overpayment_or_overflow(): void
    {
        $order = $this->order();
        foreach (['9.651', '0.001', 0, -1, '1e100', '92233720368547758.08'] as $amount) {
            $this->postJson('/admin/order/add-payment/' . $order->id, ['amount' => $amount])
                ->assertUnprocessable()->assertJsonValidationErrors('amount');
            $this->putJson('/admin/order/update-payment/' . $order->payments()->first()->id, ['amount' => $amount])
                ->assertUnprocessable()->assertJsonValidationErrors('amount');
        }
        $this->assertSame(10.0, $order->fresh()->paid_amount);
    }

    public function test_creation_records_deposit_and_allows_exact_remaining_payment(): void
    {
        $response = $this->postJson('/admin/order/save', $this->creationPayload(10))
            ->assertOk()->assertJsonPath('payment_status', 'Partial');
        $order = Order::findOrFail($response->json('id'));
        $this->assertSame(19.65, $order->total_price);
        $this->assertSame(10.0, $order->paid_amount);
        $this->assertSame('Deposit', $order->payments()->first()->note);
        $this->postJson('/admin/order/add-payment/' . $order->id, ['amount' => 9.65])
            ->assertOk()->assertJsonPath('payment_status', 'Paid')->assertJsonPath('remaining_amount', 0);
    }

    public function test_creation_can_pay_the_displayed_total_despite_cart_float_noise(): void
    {
        $this->postJson('/admin/order/save', $this->creationPayload(0.3, 0.1 + 0.2))
            ->assertOk()->assertJsonPath('payment_status', 'Paid');
        $this->assertSame(0.3, Order::firstOrFail()->paid_amount);
    }

    public function test_creation_rejects_sub_cent_and_excess_payments_without_saving_an_order(): void
    {
        foreach ([19.66, '19.651', '1e100'] as $amount) {
            $this->postJson('/admin/order/save', $this->creationPayload($amount))
                ->assertUnprocessable()->assertJsonValidationErrors('partial_payment_amount');
        }
        $this->assertSame(0, Order::count());
        $this->assertSame(0, OrderPayment::count());
    }

    public function test_order_details_use_the_same_normalized_balance_as_payment_validation(): void
    {
        $order = $this->order();
        $data = app(OrderController::class)->show($order->id)->getData();
        $this->assertSame(9.65, $data['paymentRemainingAmount']);
        $this->assertTrue($data['canAddPayment']);
        $this->get('/admin/order/detail/' . $order->id)->assertOk()
            ->assertSee('max="9.65"', false)
            ->assertSee('paymentForm.amount = 9.65', false)
            ->assertSee('amount: 9.65,', false);
    }

    private function discountedCartPayload(float $payment = 0): array
    {
        $fixture = json_decode(file_get_contents(__DIR__ . '/../Fixtures/order-pricing.json'), true)[0];
        Schema::create('shop_products', function (Blueprint $table) {
            $table->id();
            $table->integer('shop_id');
            $table->integer('product_id');
            $table->double('price');
            $table->softDeletes();
        });
        foreach (['stock_on_hands', 'stock_outs', 'stock_histories'] as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id();
                foreach (['product_id', 'shop_id', 'to_id', 'qty', 'current_stock', 'request_by',
                    'transfer_id', 'stock_id', 'stock_in', 'stock_out'] as $column) {
                    $table->integer($column)->nullable();
                }
                foreach (['type', 'status', 'transfer_type', 'request_by_type'] as $column) {
                    $table->string($column)->nullable();
                }
                $table->timestamps();
                $table->softDeletes();
            });
        }
        foreach ($fixture['items'] as $item) {
            DB::table('products')->insert(['id' => $item['product_id'], 'name' => 'NO-0013 item ' . $item['product_id']]);
            DB::table('shop_products')->insert([
                'shop_id' => 1, 'product_id' => $item['product_id'], 'price' => $item['itemData']['price'],
            ]);
            DB::table('stock_on_hands')->insert([
                'shop_id' => 1, 'product_id' => $item['product_id'], 'current_stock' => 100,
            ]);
        }

        return array_merge($this->creationPayload($payment), [
            'subTotal' => 61.28,
            // Submitted summaries must never override prices calculated from items.
            'total' => 999, 'total_discount' => 999, 'commissionTotal' => 999,
            'dataCarts' => json_encode($fixture['items']),
        ]);
    }

    public function test_no_0013_cart_saves_and_displays_the_discounted_total_and_correct_balance(): void
    {
        $payload = $this->discountedCartPayload();
        Order::create(['invoice_number' => 'NO-0012']);
        $response = $this->postJson('/admin/order/save', $payload)->assertOk();
        $order = Order::findOrFail($response->json('id'));
        $this->assertSame('NO-0013', $order->invoice_number);
        $this->assertSame(41.28, $order->total_price);
        $this->assertSame(20.0, $order->total_discount);
        $this->assertSame(0.0, $order->total_commission);
        $this->assertSame(0.0, $order->paid_amount);
        $this->assertSame(0, $order->payments()->count());
        $this->assertEquals([20, 1, 1], $order->orderDetails->pluck('qty')->all());
        $this->assertEquals(80, DB::table('stock_on_hands')->where('product_id', 4)->value('current_stock'));

        $this->get('/admin/order/detail/' . $order->id)->assertOk()
            ->assertSee('$35.80')->assertSee('-$20.00')->assertSee('$61.28')
            ->assertSee('$41.28')->assertDontSee('$81.28')->assertDontSee('54.80')
            ->assertSee('class="cell-total">35.80</td>', false)
            ->assertSee('class="td-disc">-1.00</td>', false)
            ->assertSee('class="td-net">1.79</td>', false)
            ->assertSee('class="td-total">35.80</td>', false);
        $this->getJson('/admin/order/payment-details/' . $order->id)->assertOk()
            ->assertJsonPath('total_price', 41.28)->assertJsonPath('paid_amount', 0)
            ->assertJsonPath('remaining_amount', 41.28);

        // Updating a Pending order uses the same pricing and preserves stock effects.
        $payload['id'] = $order->id;
        $payload['partial_payment_amount'] = 10;
        $this->postJson('/admin/order/save', $payload)->assertOk()->assertJsonPath('payment_status', 'Partial');
        $this->assertSame(41.28, $order->fresh()->total_price);
        $this->assertEquals(80, DB::table('stock_on_hands')->where('product_id', 4)->value('current_stock'));
        $this->getJson('/admin/order/payment-details/' . $order->id)->assertOk()
            ->assertJsonPath('paid_amount', 10)->assertJsonPath('remaining_amount', 31.28);
        $this->postJson('/admin/order/add-payment/' . $order->id, ['amount' => 31.29])
            ->assertUnprocessable()->assertJsonValidationErrors('amount');
        $this->postJson('/admin/order/add-payment/' . $order->id, ['amount' => 31.28])
            ->assertOk()->assertJsonPath('paid_amount', 41.28)
            ->assertJsonPath('remaining_amount', 0)->assertJsonPath('payment_status', 'Paid');
    }

    public function test_discounted_creation_rejects_the_gross_payment_and_rolls_back_stock(): void
    {
        $payload = $this->discountedCartPayload(61.28);
        $this->postJson('/admin/order/save', $payload)
            ->assertUnprocessable()->assertJsonValidationErrors('partial_payment_amount');
        $this->assertSame(0, Order::count());
        $this->assertSame(0, OrderPayment::count());
        $this->assertSame(0, DB::table('order_details')->count());
        $this->assertSame(0, DB::table('stock_outs')->count());
        $this->assertEquals(100, DB::table('stock_on_hands')->where('product_id', 4)->value('current_stock'));

        $payload['partial_payment_amount'] = 41.28;
        $this->postJson('/admin/order/save', $payload)->assertOk()->assertJsonPath('payment_status', 'Paid');
        $this->assertSame(41.28, Order::firstOrFail()->paid_amount);
        $this->assertEquals(0, Order::firstOrFail()->remaining_amount);
    }

    public function test_decimal_item_discounts_survive_create_edit_and_reload(): void
    {
        $payload = $this->discountedCartPayload();
        $items = [
            ['product_id' => 4, 'product_type' => 'product', 'product_qty' => 3,
                'itemData' => ['price' => 2.79, 'discount' => '0.50', 'discountType' => 'usd']],
            ['product_id' => 1, 'product_type' => 'service', 'product_qty' => 1,
                'itemData' => ['price' => 20, 'discount' => '0.50', 'discountType' => 'usd']],
        ];
        $payload['dataCarts'] = json_encode($items);
        $response = $this->postJson('/admin/order/save', $payload)->assertOk();
        $order = Order::findOrFail($response->json('id'));
        $this->assertSame(26.37, $order->total_price);
        $this->assertSame(2.0, $order->total_discount);
        $this->assertSame(0.5, $order->orderDetails->first()->product_discount);
        $this->assertSame(0.5, $order->orderDetails->last()->service_discount);
        $this->get('/admin/order/detail/' . $order->id)->assertOk()
            ->assertSee('class="td-disc">-0.50</td>', false)
            ->assertSee('$26.37');

        $view = app(OrderController::class)->edit($order->id);
        $this->assertSame('admin::pages.order.createOrder', $view->name());
        $this->assertSame(0.5, $view->getData()['order']->orderDetails->first()->product_discount);
        $this->assertSame(0.5, $view->getData()['order']->orderDetails->last()->service_discount);

        $payload['id'] = $order->id;
        $items[0]['itemData']['discount'] = '0.75';
        $items[1]['itemData']['discount'] = '12.5';
        $items[1]['itemData']['discountType'] = 'percent';
        $payload['dataCarts'] = json_encode($items);
        $this->postJson('/admin/order/save', $payload)->assertOk();
        $order->refresh();
        $this->assertSame(23.62, $order->total_price);
        $this->assertSame(4.75, $order->total_discount);
        $this->assertSame(0.75, $order->orderDetails->first()->product_discount);
        $this->assertSame(12.5, $order->orderDetails->last()->service_discount);
        $this->assertEquals(97, DB::table('stock_on_hands')->where('product_id', 4)->value('current_stock'));

        foreach ($items as &$item) {
            $item['itemData']['discount'] = '';
        }
        unset($item);
        $payload['dataCarts'] = json_encode($items);
        $this->postJson('/admin/order/save', $payload)->assertOk();
        $this->assertSame(28.37, $order->fresh()->total_price);
        $this->assertSame(0.0, $order->fresh()->total_discount);
    }

    public function test_invalid_item_discounts_cannot_create_or_change_an_order(): void
    {
        $payload = $this->creationPayload(0);
        $items = json_decode($payload['dataCarts'], true);
        $items[0]['itemData']['discountType'] = 'usd';
        foreach (['-0.50', 'invalid', '0.5oops', '1e999', true, []] as $discount) {
            $items[0]['itemData']['discount'] = $discount;
            $payload['dataCarts'] = json_encode($items);
            $this->postJson('/admin/order/save', $payload)
                ->assertUnprocessable()->assertJsonValidationErrors('dataCarts');
        }
        $this->assertSame(0, Order::count());
        $this->assertSame(0, DB::table('order_details')->count());

        $items[0]['itemData']['discount'] = '0.50';
        $payload['dataCarts'] = json_encode($items);
        $response = $this->postJson('/admin/order/save', $payload)->assertOk();
        $order = Order::findOrFail($response->json('id'));
        $payload['id'] = $order->id;
        $items[0]['itemData']['discount'] = '-0.25';
        $payload['dataCarts'] = json_encode($items);
        $this->postJson('/admin/order/save', $payload)
            ->assertUnprocessable()->assertJsonValidationErrors('dataCarts');
        $this->assertSame(19.15, $order->fresh()->total_price);
        $this->assertSame(0.5, $order->fresh()->orderDetails->first()->service_discount);
    }

    public function test_service_percentage_discount_and_commission_are_saved_before_payment(): void
    {
        $payload = $this->creationPayload(16);
        $payload['subTotal'] = 20;
        $payload['dataCarts'] = json_encode([[
            'product_id' => 1, 'product_type' => 'service', 'product_qty' => 1,
            'itemData' => ['price' => 20, 'discount' => 10, 'discountType' => 'percent',
                'commission' => 10, 'commissionType' => 'percent'],
        ]]);
        $response = $this->postJson('/admin/order/save', $payload)->assertOk()->assertJsonPath('payment_status', 'Paid');
        $order = Order::findOrFail($response->json('id'));
        $this->assertSame(16.0, $order->total_price);
        $this->assertSame(2.0, $order->total_discount);
        $this->assertSame(2.0, $order->total_commission);
        $this->assertSame(16.0, $order->paid_amount);
        $this->get('/admin/order/detail/' . $order->id)->assertOk()
            ->assertSee('$20.00')->assertSee('$18.00')->assertSee('-$2.00')->assertSee('$16.00')
            ->assertSee('class="cell-total">18.00</td>', false)
            ->assertSee('class="td-disc">-2.00</td>', false)
            ->assertSee('class="td-net">18.00</td>', false)
            ->assertSee('class="td-total">18.00</td>', false);
    }

    public function test_fully_discounted_order_has_zero_total_and_no_payment_due(): void
    {
        $payload = $this->creationPayload(0);
        $payload['dataCarts'] = json_encode([[
            'product_id' => 1, 'product_type' => 'service', 'product_qty' => 1,
            'itemData' => ['price' => 19.65, 'discount' => 100, 'discountType' => 'percent'],
        ]]);
        $response = $this->postJson('/admin/order/save', $payload)->assertOk();
        $order = Order::findOrFail($response->json('id'));
        $this->assertSame(0.0, $order->total_price);
        $this->assertSame(19.65, $order->total_discount);
        $data = app(OrderController::class)->show($order->id)->getData();
        $this->assertEquals(0, $data['paymentRemainingAmount']);
        $this->assertFalse($data['canAddPayment']);
        $this->postJson('/admin/order/add-payment/' . $order->id, ['amount' => 0.01])
            ->assertUnprocessable()->assertJsonValidationErrors('amount');
        $this->assertSame(0, $order->payments()->count());
    }
}
