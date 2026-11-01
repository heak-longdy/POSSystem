<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\OrderController;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrderAddPaymentNoteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        require_once database_path('migrations/2026_08_02_231335_create_permission_tables.php');
        (new \CreatePermissionTables())->up();

        foreach (['users', 'customers', 'shops', 'services', 'barbers', 'products'] as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('phone')->nullable();
                $table->boolean('status')->default(true);
                $table->softDeletes();
            });
            DB::table($name)->insert(['id' => 1, 'name' => 'Payment test']);
        }

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id')->nullable();
            $table->integer('shop_id')->nullable();
            $table->integer('created_by')->nullable();
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
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('order_balance_transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('new_order_id');
            $table->unsignedBigInteger('previous_order_id');
            $table->decimal('transferred_amount', 15, 2)->default(0);
            $table->string('original_payment_status', 20)->default('Pending');
            $table->dateTime('transferred_at')->nullable();
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
        $migration = require database_path('migrations/2026_10_25_000000_add_automatic_product_pricing_to_order_details_table.php');
        $migration->up();
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

    public function test_payment_details_endpoint_returns_note_from_only_most_recent_payment(): void
    {
        $order = Order::create([
            'invoice_number' => 'NO-0010',
            'total_price' => 100.0,
            'paid_amount' => 30.0,
            'payment_status' => 'Partial',
        ]);

        // First payment
        OrderPayment::create([
            'order_id' => $order->id,
            'amount' => 10.0,
            'note' => 'First deposit note',
            'created_at' => Carbon::now()->subDays(2),
        ]);

        // Second payment (most recent)
        OrderPayment::create([
            'order_id' => $order->id,
            'amount' => 20.0,
            'note' => 'Second installment note',
            'created_at' => Carbon::now()->subDay(),
        ]);

        $res = $this->getJson('/admin/order/payment-details/' . $order->id)
            ->assertOk()
            ->assertJsonPath('latest_payment_note', 'Second installment note');

        $this->assertSame('Second installment note', $res->json('latest_payment_note'));
    }

    public function test_payment_details_does_not_combine_multiple_notes(): void
    {
        $order = Order::create([
            'invoice_number' => 'NO-0011',
            'total_price' => 100.0,
            'paid_amount' => 50.0,
            'payment_status' => 'Partial',
        ]);

        OrderPayment::create([
            'order_id' => $order->id,
            'amount' => 25.0,
            'note' => 'Note Alpha',
            'created_at' => Carbon::parse('2026-05-01 10:00:00'),
        ]);

        OrderPayment::create([
            'order_id' => $order->id,
            'amount' => 25.0,
            'note' => 'Note Beta',
            'created_at' => Carbon::parse('2026-05-02 10:00:00'),
        ]);

        $this->getJson('/admin/order/payment-details/' . $order->id)
            ->assertOk()
            ->assertJsonPath('latest_payment_note', 'Note Beta');
    }

    public function test_payment_details_returns_empty_when_latest_payment_has_no_note(): void
    {
        $order = Order::create([
            'invoice_number' => 'NO-0012',
            'total_price' => 100.0,
            'paid_amount' => 50.0,
            'payment_status' => 'Partial',
        ]);

        // Older payment has a note
        OrderPayment::create([
            'order_id' => $order->id,
            'amount' => 25.0,
            'note' => 'Note from older payment',
            'created_at' => Carbon::parse('2026-05-01 10:00:00'),
        ]);

        // Latest payment has null note
        OrderPayment::create([
            'order_id' => $order->id,
            'amount' => 25.0,
            'note' => null,
            'created_at' => Carbon::parse('2026-05-02 10:00:00'),
        ]);

        // Should NOT fall back to older payment note
        $this->getJson('/admin/order/payment-details/' . $order->id)
            ->assertOk()
            ->assertJsonPath('latest_payment_note', '');
    }

    public function test_payment_details_returns_empty_when_no_payment_records_exist(): void
    {
        $order = Order::create([
            'invoice_number' => 'NO-0013',
            'total_price' => 100.0,
            'paid_amount' => 0,
            'payment_status' => 'Pending',
        ]);

        $this->getJson('/admin/order/payment-details/' . $order->id)
            ->assertOk()
            ->assertJsonPath('latest_payment_note', '');
    }

    public function test_latest_payment_ignores_soft_deleted_payments(): void
    {
        $order = Order::create([
            'invoice_number' => 'NO-0014',
            'total_price' => 100.0,
            'paid_amount' => 25.0,
            'payment_status' => 'Partial',
        ]);

        OrderPayment::create([
            'order_id' => $order->id,
            'amount' => 25.0,
            'note' => 'Active payment note',
            'created_at' => Carbon::parse('2026-05-01 10:00:00'),
        ]);

        $deletedPayment = OrderPayment::create([
            'order_id' => $order->id,
            'amount' => 25.0,
            'note' => 'Deleted payment note',
            'created_at' => Carbon::parse('2026-05-02 10:00:00'),
        ]);
        $deletedPayment->delete();

        $this->getJson('/admin/order/payment-details/' . $order->id)
            ->assertOk()
            ->assertJsonPath('latest_payment_note', 'Active payment note');
    }

    public function test_order_detail_view_populates_latest_payment_note(): void
    {
        $order = Order::create([
            'invoice_number' => 'NO-0015',
            'total_price' => 100.0,
            'paid_amount' => 25.0,
            'payment_status' => 'Partial',
        ]);

        OrderPayment::create([
            'order_id' => $order->id,
            'amount' => 25.0,
            'note' => 'Detail view note test',
            'created_at' => Carbon::now(),
        ]);

        $data = app(OrderController::class)->show($order->id)->getData();
        $this->assertSame('Detail view note test', $data['latestPaymentNote']);

        $response = $this->get('/admin/order/detail/' . $order->id)->assertOk();
        $response->assertSee('Detail view note test', false);
        $response->assertSee('openAddPaymentModal()', false);
    }

    public function test_order_detail_view_leaves_note_empty_when_no_previous_payment(): void
    {
        $order = Order::create([
            'invoice_number' => 'NO-0016',
            'total_price' => 100.0,
            'paid_amount' => 0.0,
            'payment_status' => 'Pending',
        ]);

        $data = app(OrderController::class)->show($order->id)->getData();
        $this->assertSame('', $data['latestPaymentNote']);

        $response = $this->get('/admin/order/detail/' . $order->id)->assertOk();
        $response->assertSee('const latestNote = "";', false);
        $response->assertSee('latestPaymentNote: latestNote', false);
    }

    public function test_latest_payment_uses_highest_id_when_creation_date_is_identical(): void
    {
        $order = Order::create([
            'invoice_number' => 'NO-0017',
            'total_price' => 100.0,
            'paid_amount' => 40.0,
            'payment_status' => 'Partial',
        ]);

        $sameTime = Carbon::parse('2026-06-01 12:00:00');

        $p1 = OrderPayment::create([
            'order_id' => $order->id,
            'amount' => 20.0,
            'note' => 'Payment with lower ID',
            'created_at' => $sameTime,
        ]);

        $p2 = OrderPayment::create([
            'order_id' => $order->id,
            'amount' => 20.0,
            'note' => 'Payment with higher ID',
            'created_at' => $sameTime,
        ]);

        $this->assertTrue($p2->id > $p1->id);

        $res = $this->getJson('/admin/order/payment-details/' . $order->id)->assertOk();
        $this->assertSame('Payment with higher ID', $res->json('latest_payment_note'));
    }

    public function test_user_can_edit_or_clear_note_when_adding_payment(): void
    {
        $order = Order::create([
            'invoice_number' => 'NO-0018',
            'total_price' => 100.0,
            'paid_amount' => 20.0,
            'payment_status' => 'Partial',
        ]);

        OrderPayment::create([
            'order_id' => $order->id,
            'amount' => 20.0,
            'note' => 'Original note to be edited',
            'created_at' => Carbon::now(),
        ]);

        // Submit with edited note
        $this->postJson('/admin/order/add-payment/' . $order->id, [
            'amount' => 30.0,
            'payment_method' => 'Cash',
            'payment_date' => Carbon::now()->format('Y-m-d H:i:s'),
            'note' => 'Custom modified note by user',
        ])->assertOk();

        $latest = $order->fresh()->getLatestPaymentRecord();
        $this->assertSame('Custom modified note by user', $latest->note);

        // Submit another payment with cleared (empty) note
        $this->postJson('/admin/order/add-payment/' . $order->id, [
            'amount' => 20.0,
            'payment_method' => 'Cash',
            'payment_date' => Carbon::now()->format('Y-m-d H:i:s'),
            'note' => '',
        ])->assertOk();

        $newLatest = $order->fresh()->getLatestPaymentRecord();
        $this->assertSame('', (string) $newLatest->note);
    }

    public function test_order_list_eager_loads_and_decorates_latest_payment_note(): void
    {
        $order = Order::create([
            'invoice_number' => 'NO-0019',
            'total_price' => 100.0,
            'paid_amount' => 20.0,
            'payment_status' => 'Partial',
            'order_date' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        OrderPayment::create([
            'order_id' => $order->id,
            'amount' => 20.0,
            'note' => 'List page note population test',
            'created_at' => Carbon::now(),
        ]);

        $response = $this->get('/admin/order/list/Partial')->assertOk();
        $response->assertSee('List page note population test', false);
    }

    public function test_payment_history_shows_edit_button_only_for_latest_payment(): void
    {
        $order = Order::create([
            'invoice_number' => 'NO-0020',
            'total_price' => 100.0,
            'paid_amount' => 30.0,
            'payment_status' => 'Partial',
        ]);

        $p1 = OrderPayment::create([
            'order_id' => $order->id,
            'amount' => 10.0,
            'note' => 'Older payment record',
            'created_at' => Carbon::parse('2026-06-01 10:00:00'),
        ]);

        $p2 = OrderPayment::create([
            'order_id' => $order->id,
            'amount' => 20.0,
            'note' => 'Latest payment record',
            'created_at' => Carbon::parse('2026-06-02 10:00:00'),
        ]);

        $response = $this->get('/admin/order/detail/' . $order->id)->assertOk();
        $html = $response->getContent();

        // Count occurrences of btn-action-edit in the response
        $editButtonCount = substr_count($html, 'class="btn-action-edit"');
        $this->assertSame(1, $editButtonCount, 'Expected exactly 1 edit action button in payment history');

        // Verify the edit button is for p2, not p1
        $this->assertStringContainsString('openEditPaymentModal', $html);
        $this->assertStringContainsString('\u0022id\u0022:' . $p2->id, $html);
        $this->assertStringNotContainsString('\u0022id\u0022:' . $p1->id, $html);
    }

    public function test_editing_latest_payment_recalculates_balance_and_status(): void
    {
        $order = Order::create([
            'invoice_number' => 'NO-0021',
            'total_price' => 100.0,
            'paid_amount' => 50.0,
            'payment_status' => 'Partial',
        ]);

        $p1 = OrderPayment::create([
            'order_id' => $order->id,
            'amount' => 30.0,
            'created_at' => Carbon::parse('2026-06-01 10:00:00'),
        ]);

        $p2 = OrderPayment::create([
            'order_id' => $order->id,
            'amount' => 20.0,
            'created_at' => Carbon::parse('2026-06-02 10:00:00'),
        ]);

        // Increase p2 to 70 to fully pay the order (30 + 70 = 100)
        $this->putJson('/admin/order/update-payment/' . $p2->id, [
            'amount' => 70.0,
            'payment_method' => 'ABA',
            'payment_date' => '2026-06-03 14:00:00',
            'note' => 'Paid in full after edit',
        ])->assertOk()
            ->assertJsonPath('payment_status', 'Paid')
            ->assertJsonPath('paid_amount', 100)
            ->assertJsonPath('remaining_amount', 0);

        $order->refresh();
        $this->assertSame('Paid', $order->payment_status);
        $this->assertSame(100.0, (float) $order->paid_amount);
        $this->assertSame(0.0, (float) $order->remaining_amount);
        $this->assertSame('Paid in full after edit', $p2->fresh()->note);
        $this->assertSame('ABA', $p2->fresh()->payment_method);
    }

    public function test_editing_latest_payment_rejects_amount_exceeding_available_balance(): void
    {
        $order = Order::create([
            'invoice_number' => 'NO-0022',
            'total_price' => 100.0,
            'paid_amount' => 50.0,
            'payment_status' => 'Partial',
        ]);

        OrderPayment::create([
            'order_id' => $order->id,
            'amount' => 30.0,
            'created_at' => Carbon::parse('2026-06-01 10:00:00'),
        ]);

        $p2 = OrderPayment::create([
            'order_id' => $order->id,
            'amount' => 20.0,
            'created_at' => Carbon::parse('2026-06-02 10:00:00'),
        ]);

        // Available balance is 50 remaining + 20 current = 70. Amount 70.01 must be rejected.
        $this->putJson('/admin/order/update-payment/' . $p2->id, [
            'amount' => 70.01,
        ])->assertUnprocessable()->assertJsonValidationErrors('amount');

        // Order remains unchanged
        $order->refresh();
        $this->assertSame('Partial', $order->payment_status);
        $this->assertSame(50.0, (float) $order->paid_amount);
    }

    public function test_payment_history_shows_delete_button_only_for_latest_payment(): void
    {
        $order = Order::create([
            'invoice_number' => 'NO-0023',
            'total_price' => 100.0,
            'paid_amount' => 30.0,
            'payment_status' => 'Partial',
        ]);

        $p1 = OrderPayment::create([
            'order_id' => $order->id,
            'amount' => 10.0,
            'note' => 'First payment',
            'created_at' => Carbon::parse('2026-06-01 10:00:00'),
        ]);

        $p2 = OrderPayment::create([
            'order_id' => $order->id,
            'amount' => 20.0,
            'note' => 'Second payment',
            'created_at' => Carbon::parse('2026-06-02 10:00:00'),
        ]);

        $response = $this->get('/admin/order/detail/' . $order->id)->assertOk();
        $html = $response->getContent();

        // Count occurrences of btn-action-delete in the response
        $deleteButtonCount = substr_count($html, 'class="btn-action-delete"');
        $this->assertSame(1, $deleteButtonCount, 'Expected exactly 1 delete action button in payment history');

        // Verify the delete button targets p2, not p1
        $this->assertStringContainsString('deletePaymentRecord(' . $p2->id . ',', $html);
        $this->assertStringNotContainsString('deletePaymentRecord(' . $p1->id . ',', $html);
    }

    public function test_deleting_older_payment_is_rejected_until_it_becomes_latest(): void
    {
        $order = Order::create([
            'invoice_number' => 'NO-0024',
            'total_price' => 100.0,
            'paid_amount' => 30.0,
            'payment_status' => 'Partial',
        ]);

        $p1 = OrderPayment::create([
            'order_id' => $order->id,
            'amount' => 10.0,
            'created_at' => Carbon::parse('2026-06-01 10:00:00'),
        ]);

        $p2 = OrderPayment::create([
            'order_id' => $order->id,
            'amount' => 20.0,
            'created_at' => Carbon::parse('2026-06-02 10:00:00'),
        ]);

        // Attempting to delete p1 when p2 exists must be rejected
        $this->deleteJson('/admin/order/delete-payment/' . $p1->id)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('payment_id');

        // Deleting p2 succeeds because it is the latest payment
        $this->deleteJson('/admin/order/delete-payment/' . $p2->id)
            ->assertOk()
            ->assertJsonPath('paid_amount', 10)
            ->assertJsonPath('remaining_amount', 90);

        // Now p1 is the latest active payment, so deleting p1 succeeds
        $this->deleteJson('/admin/order/delete-payment/' . $p1->id)
            ->assertOk()
            ->assertJsonPath('paid_amount', 0)
            ->assertJsonPath('remaining_amount', 100);

        $this->assertSame(0, $order->payments()->count());
    }

    public function test_delivery_slip_displays_note_from_only_latest_payment_record(): void
    {
        $order = Order::create([
            'invoice_number' => 'NO-0030',
            'total_price' => 200.0,
            'paid_amount' => 50.0,
            'payment_status' => 'Partial',
        ]);

        OrderPayment::create([
            'order_id' => $order->id,
            'amount' => 20.0,
            'note' => 'First older payment note',
            'created_at' => Carbon::parse('2026-06-01 10:00:00'),
        ]);

        OrderPayment::create([
            'order_id' => $order->id,
            'amount' => 30.0,
            'note' => 'Latest payment note for delivery',
            'created_at' => Carbon::parse('2026-06-02 10:00:00'),
        ]);

        $html = view('admin::pages.order.invoice-template-02', [
            'order' => $order->fresh(),
            'printLocale' => 'en',
        ])->render();

        $this->assertStringContainsString('<strong>Note:</strong> Latest payment note for delivery', $html);
        $this->assertStringNotContainsString('First older payment note', $html);
        $this->assertStringNotContainsString('Free of charge items', $html);
    }

    public function test_delivery_slip_displays_translated_note_label_in_khmer(): void
    {
        $order = Order::create([
            'invoice_number' => 'NO-0031',
            'total_price' => 150.0,
            'paid_amount' => 50.0,
            'payment_status' => 'Partial',
        ]);

        OrderPayment::create([
            'order_id' => $order->id,
            'amount' => 50.0,
            'note' => 'ប្រាក់កក់តាម ABA',
            'created_at' => Carbon::parse('2026-06-03 10:00:00'),
        ]);

        $html = view('admin::pages.order.invoice-template-02', [
            'order' => $order->fresh(),
            'printLocale' => 'km',
        ])->render();

        $this->assertStringContainsString('<strong>ចំណាំ:</strong> ប្រាក់កក់តាម ABA', $html);
        $this->assertStringNotContainsString('ផលិតផលមិនគិតថ្លៃ', $html);
    }

    public function test_delivery_slip_does_not_combine_notes_from_multiple_payments(): void
    {
        $order = Order::create([
            'invoice_number' => 'NO-0032',
            'total_price' => 300.0,
            'paid_amount' => 90.0,
            'payment_status' => 'Partial',
        ]);

        OrderPayment::create([
            'order_id' => $order->id,
            'amount' => 30.0,
            'note' => 'Payment A',
            'created_at' => Carbon::parse('2026-06-01 09:00:00'),
        ]);

        OrderPayment::create([
            'order_id' => $order->id,
            'amount' => 30.0,
            'note' => 'Payment B',
            'created_at' => Carbon::parse('2026-06-02 09:00:00'),
        ]);

        OrderPayment::create([
            'order_id' => $order->id,
            'amount' => 30.0,
            'note' => 'Payment C',
            'created_at' => Carbon::parse('2026-06-03 09:00:00'),
        ]);

        $html = view('admin::pages.order.invoice-template-02', [
            'order' => $order->fresh(),
            'printLocale' => 'en',
        ])->render();

        $this->assertStringContainsString('Payment C', $html);
        $this->assertStringNotContainsString('Payment A', $html);
        $this->assertStringNotContainsString('Payment B', $html);
    }

    public function test_delivery_slip_leaves_note_section_empty_when_no_payments(): void
    {
        $order = Order::create([
            'invoice_number' => 'NO-0033',
            'total_price' => 100.0,
            'paid_amount' => 0.0,
            'payment_status' => 'Pending',
        ]);

        $html = view('admin::pages.order.invoice-template-02', [
            'order' => $order->fresh(),
            'printLocale' => 'en',
        ])->render();

        $this->assertStringNotContainsString('<strong>Note:</strong>', $html);
        $this->assertStringNotContainsString('slip-free-goods-note', $html);
        $this->assertStringNotContainsString('Free of charge items', $html);
    }

    public function test_delivery_slip_leaves_note_section_empty_when_latest_payment_note_is_blank(): void
    {
        $order = Order::create([
            'invoice_number' => 'NO-0034',
            'total_price' => 100.0,
            'paid_amount' => 20.0,
            'payment_status' => 'Partial',
        ]);

        // Older payment has note, but latest has null/whitespace note
        OrderPayment::create([
            'order_id' => $order->id,
            'amount' => 10.0,
            'note' => 'Older note',
            'created_at' => Carbon::parse('2026-06-01 10:00:00'),
        ]);

        OrderPayment::create([
            'order_id' => $order->id,
            'amount' => 10.0,
            'note' => '   ',
            'created_at' => Carbon::parse('2026-06-02 10:00:00'),
        ]);

        $html = view('admin::pages.order.invoice-template-02', [
            'order' => $order->fresh(),
            'printLocale' => 'en',
        ])->render();

        $this->assertStringNotContainsString('Older note', $html);
        $this->assertStringNotContainsString('<strong>Note:</strong>', $html);
        $this->assertStringNotContainsString('slip-free-goods-note', $html);
    }

    public function test_delivery_slip_preserves_multiline_notes_and_escapes_html(): void
    {
        $order = Order::create([
            'invoice_number' => 'NO-0035',
            'total_price' => 100.0,
            'paid_amount' => 50.0,
            'payment_status' => 'Partial',
        ]);

        OrderPayment::create([
            'order_id' => $order->id,
            'amount' => 50.0,
            'note' => "Line 1\n<script>alert('xss')</script>Line 2",
            'created_at' => Carbon::parse('2026-06-01 10:00:00'),
        ]);

        $html = view('admin::pages.order.invoice-template-02', [
            'order' => $order->fresh(),
            'printLocale' => 'en',
        ])->render();

        $this->assertStringContainsString('Line 1<br />', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(&#039;xss&#039;)&lt;/script&gt;Line 2', $html);
        $this->assertStringNotContainsString('<script>alert', $html);
    }

    public function test_print_invoice_template_is_not_modified_with_payment_note_section(): void
    {
        $order = Order::create([
            'invoice_number' => 'NO-0036',
            'total_price' => 100.0,
            'paid_amount' => 50.0,
            'payment_status' => 'Partial',
        ]);

        OrderPayment::create([
            'order_id' => $order->id,
            'amount' => 50.0,
            'note' => 'Delivery slip note only',
            'created_at' => Carbon::parse('2026-06-01 10:00:00'),
        ]);

        $html = view('admin::pages.order.invoice-template', [
            'order' => $order->fresh(),
            'printLocale' => 'en',
        ])->render();

        $this->assertStringNotContainsString('Delivery slip note only', $html);
    }
}
