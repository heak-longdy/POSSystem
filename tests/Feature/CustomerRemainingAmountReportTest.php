<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomerRemainingAmountReportTest extends TestCase
{
    private const REPORT = '/admin/report/customer-remaining-amount';

    protected function setUp(): void
    {
        parent::setUp();
        require_once database_path('migrations/2026_08_02_231335_create_permission_tables.php');
        (new \CreatePermissionTables())->up();
        // A small, isolated schema for this read-only report. The historical
        // application migrations include MySQL-specific table renames.
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->softDeletes();
        });
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->softDeletes();
        });
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id')->nullable();
            $table->integer('shop_id')->nullable();
            $table->double('total_price')->default(0);
            $table->double('paid_amount')->default(0);
            $table->string('payment_status')->default('Pending');
            $table->string('invoice_number')->nullable();
            $table->string('pay_way')->nullable();
            $table->text('remark')->nullable();
            $table->dateTime('order_date')->nullable();
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
        DB::table('shops')->insert([['id' => 1, 'name' => 'Main Shop'], ['id' => 2, 'name' => 'Second Shop']]);
        DB::table('customers')->insert([
            ['id' => 1, 'name' => 'Sok <script>alert(1)</script>', 'phone' => '012345678', 'deleted_at' => null],
            ['id' => 2, 'name' => 'Settled customer', 'phone' => '098765432', 'deleted_at' => null],
            ['id' => 3, 'name' => 'Archived customer', 'phone' => null, 'deleted_at' => '2026-02-01'],
        ]);
        $this->actingAs(new User(['name' => 'Report admin', 'role' => 'super_admin', 'language_preference' => 'en']));
    }

    private function order(array $attributes = []): int
    {
        return DB::table('orders')->insertGetId(array_merge([
            'customer_id' => 1, 'shop_id' => 1, 'total_price' => 100, 'paid_amount' => 0,
            'payment_status' => 'Pending', 'order_date' => '2026-01-01 10:00:00',
            'created_at' => '2026-01-01 10:00:00', 'updated_at' => '2026-01-01 10:00:00',
        ], $attributes));
    }

    private function payment(int $order, float $amount, string $date, array $attributes = []): void
    {
        DB::table('order_payments')->insert(array_merge([
            'order_id' => $order, 'amount' => $amount, 'payment_method' => 'Cash',
            'payment_date' => $date, 'created_at' => $date, 'updated_at' => $date,
        ], $attributes));
    }

    public function test_report_groups_by_customer_and_excludes_rejected_deleted_and_walk_in_orders(): void
    {
        $this->order(['paid_amount' => 30, 'payment_status' => 'Partial']);
        $this->order(['total_price' => 50, 'shop_id' => 2]);
        $this->order(['total_price' => 250, 'paid_amount' => 250, 'payment_status' => 'Paid']);
        $this->order(['customer_id' => 2, 'paid_amount' => 100, 'payment_status' => 'Paid']);
        $this->order(['customer_id' => 3, 'total_price' => 20, 'paid_amount' => 5, 'payment_status' => 'Partial']);
        $this->order(['payment_status' => 'Cancel', 'total_price' => 999]);
        $this->order(['deleted_at' => '2026-01-02', 'total_price' => 999]);
        $this->order(['customer_id' => null, 'total_price' => 999]);
        $response = $this->get(self::REPORT)->assertOk()->assertSee('View Details');
        $response->assertDontSee('<script>alert(1)</script>', false);
        $this->assertSame(2, $response['customers']->total());
        $this->assertEquals(170, $response['summary']->total_amount);
        $this->assertEquals(35, $response['summary']->paid_amount);
        $this->assertEquals(135, $response['summary']->remaining_amount);
        $this->assertEquals(2, $response['customers']->first()->order_count);
        $this->assertEquals(150, $response['customers']->first()->total_amount);
        $this->assertEquals(30, $response['customers']->first()->paid_amount);

        $response->assertDontSee('name="balance"', false);
        // Old bookmarked filters cannot bypass the report's eligibility rule.
        foreach (['all', 'settled', 'outstanding'] as $balance) {
            $filtered = $this->get(self::REPORT . '?balance=' . $balance)->assertOk();
            $this->assertSame([1, 3], $filtered['customers']->pluck('id')->all());
            $this->assertEquals(135, $filtered['summary']->remaining_amount);
        }
        $filtered = $this->get(self::REPORT . '?search=012345678&shop_id=1')->assertOk();
        $this->assertEquals(70, $filtered['summary']->remaining_amount);
        $this->assertSame(1, $filtered['customers']->total());
        $byCustomer = $this->get(self::REPORT . '?customer_id=1&shop_id=1')->assertOk();
        $this->assertEquals(70, $byCustomer['summary']->remaining_amount);
        $this->assertSame(1, $byCustomer['customers']->total());
        $this->assertNotEmpty($byCustomer['customerList']);
        $otherShop = $this->get(self::REPORT . '?shop_id=2')->assertOk();
        $this->assertSame(1, $otherShop['customers']->total());
        $this->assertEquals(50, $otherShop['summary']->remaining_amount);
    }

    public function test_customers_with_pending_or_partial_orders_and_positive_balance_qualify(): void
    {
        foreach (range(4, 7) as $id) {
            DB::table('customers')->insert(['id' => $id, 'name' => 'Customer ' . $id]);
        }
        $this->order(['customer_id' => 1]); // Entirely unpaid (Pending).
        $this->order(['customer_id' => 2, 'paid_amount' => 100, 'payment_status' => 'Partial']); // Fully paid order.
        $this->order(['customer_id' => 2]); // Unpaid order for customer 2 (Pending).
        $this->order(['customer_id' => 3, 'paid_amount' => 99.99, 'payment_status' => 'Partial']); // Partial order.
        $this->order(['customer_id' => 4, 'paid_amount' => 110, 'payment_status' => 'Partial']); // Overpaid.
        $this->order(['customer_id' => 5, 'paid_amount' => 0, 'payment_status' => 'Partial']); // Unpaid Partial.
        $this->order(['customer_id' => 6, 'paid_amount' => 30, 'payment_status' => 'Cancel']); // Canceled.
        $this->order(['customer_id' => 7, 'paid_amount' => 30, 'payment_status' => 'Partial', 'deleted_at' => '2026-02-01']); // Archived customer.

        $response = $this->get(self::REPORT)->assertOk();
        $this->assertSame([1, 2, 3, 5, 7], $response['customers']->pluck('id')->sort()->values()->all());
        $this->assertEquals(370.01, $response['summary']->remaining_amount);
        $this->assertEquals(5, $response['summary']->customer_count);
    }

    public function test_customer_disappears_after_the_last_partial_order_is_paid_in_full(): void
    {
        $firstOrder = $this->order(['paid_amount' => 30, 'payment_status' => 'Partial']);
        $this->payment($firstOrder, 30, '2026-01-02 10:00:00');
        $secondOrder = $this->order(['total_price' => 50, 'paid_amount' => 10, 'payment_status' => 'Partial']);
        $this->payment($secondOrder, 10, '2026-01-02 10:00:00');
        $before = $this->get(self::REPORT)->assertOk();
        $this->assertSame(1, $before['customers']->total());
        $this->assertEquals(110, $before['summary']->remaining_amount);

        $this->postJson('/admin/remaining-amount/add-payment/' . $firstOrder, ['amount' => 70])
            ->assertOk()->assertJsonPath('payment_status', 'Paid');
        $partlySettled = $this->get(self::REPORT)->assertOk();
        $this->assertSame(1, $partlySettled['customers']->total());
        $this->assertEquals(40, $partlySettled['summary']->remaining_amount);
        $this->assertEquals(50, $partlySettled['summary']->total_amount);
        $this->assertEquals(10, $partlySettled['summary']->paid_amount);
        $this->assertEquals(1, $partlySettled['customers']->first()->order_count);
        $remainingHistory = $this->get(self::REPORT . '/details/1?from_date=2026-01-01&to_date=2026-01-31')->assertOk();
        $this->assertSame([$secondOrder], $remainingHistory['invoices']->pluck('order_id')->all());
        $this->assertSame(4000, $remainingHistory['history']['current']);
        $this->assertSame(4000, $remainingHistory['history']['closing']);
        $this->assertSame(5000, $remainingHistory['history']['increases']);
        $this->assertSame(1000, $remainingHistory['history']['decreases']);

        $this->postJson('/admin/remaining-amount/add-payment/' . $secondOrder, ['amount' => 40])
            ->assertOk()->assertJsonPath('payment_status', 'Paid');
        $settled = $this->get(self::REPORT . '?balance=all')->assertOk();
        $this->assertSame(0, $settled['customers']->total());
        $this->assertEquals(0, $settled['summary']->customer_count);
        $this->assertEquals(0, $settled['summary']->remaining_amount);
        $this->assertEquals(0, $settled['summary']->total_amount);
        $history = $this->get(self::REPORT . '/details/1')->assertOk();
        $this->assertSame(0, $history['history']['current']);
        $this->assertSame(0, $history['invoices']->total());
        $this->assertCount(0, $history['history']['rows']);
        $this->assertSame(0, $history['history']['increases']);
        $this->assertSame(0, $history['history']['decreases']);
        $history->assertSee('No transactions for outstanding invoices match these filters.');
    }

    public function test_history_uses_current_positive_balances_even_for_earlier_date_ranges(): void
    {
        $partial = $this->order(['invoice_number' => 'PARTIAL-1', 'paid_amount' => 99.99, 'payment_status' => 'Partial']);
        $unpaid = $this->order(['invoice_number' => 'UNPAID-1', 'total_price' => 30]);
        $fullyPaid = $this->order(['invoice_number' => 'FULLY-PAID', 'paid_amount' => 100, 'payment_status' => 'Paid']);
        $this->payment($fullyPaid, 100, '2026-02-01 10:00:00');
        $this->order(['invoice_number' => 'STALE-PARTIAL', 'paid_amount' => 100, 'payment_status' => 'Partial']);
        $this->order(['invoice_number' => 'OVERPAID', 'paid_amount' => 110, 'payment_status' => 'Paid']);
        $this->order(['invoice_number' => 'ZERO-TOTAL', 'total_price' => 0]);
        $this->order(['invoice_number' => 'REJECTED', 'payment_status' => 'Cancel']);
        $this->order(['invoice_number' => 'DELETED', 'deleted_at' => '2026-01-02']);
        $this->order(['customer_id' => 2, 'invoice_number' => 'OTHER-CUSTOMER']);

        $response = $this->get(self::REPORT . '/details/1?from_date=2026-01-01&to_date=2026-01-31&balance=all')
            ->assertOk()->assertSee('PARTIAL-1')->assertSee('UNPAID-1')
            ->assertDontSee('FULLY-PAID')->assertDontSee('STALE-PARTIAL')->assertDontSee('OVERPAID')
            ->assertDontSee('ZERO-TOTAL')->assertDontSee('REJECTED')->assertDontSee('DELETED')->assertDontSee('OTHER-CUSTOMER');
        $this->assertSame([$partial, $unpaid], $response['invoices']->pluck('order_id')->all());
        $this->assertSame(3001, $response['history']['current']);
        $this->assertSame(3001, $response['history']['closing']);
        $this->assertSame(13000, $response['history']['increases']);
        $this->assertSame(9999, $response['history']['decreases']);
    }

    public function test_history_is_customer_specific_and_excludes_deleted_payments(): void
    {
        $order = $this->order(['paid_amount' => 30, 'payment_status' => 'Partial', 'invoice_number' => 'INV-001']);
        $this->payment($order, 30, '2026-02-01 10:00:00', ['note' => 'First installment']);
        $this->payment($order, 80, '2026-02-02 10:00:00', ['deleted_at' => '2026-02-03']);
        $this->order(['customer_id' => 2, 'total_price' => 999]);
        $response = $this->get(self::REPORT . '/details/1?from_date=2026-02-01&to_date=2026-02-28')
            ->assertOk()->assertSee('First installment')->assertSee('INV-001');
        $this->assertSame(10000, $response['history']['opening']);
        $this->assertSame(7000, $response['history']['closing']);
        $this->assertSame(1, $response['invoices']->total());
        $invoice = $response['invoices']->first();
        $this->assertCount(1, $invoice['rows']);
        $this->assertSame(7000, $invoice['rows']->first()['invoice_balance']);
        $this->assertSame(10000, $invoice['opening']);
        $this->assertSame(7000, $invoice['closing']);
        $this->get(self::REPORT . '/details/999')->assertNotFound();
    }

    public function test_invoice_history_stays_together_and_preserves_shop_filters(): void
    {
        $order = $this->order(['total_price' => 100, 'paid_amount' => 55, 'payment_status' => 'Partial']);
        for ($day = 1; $day <= 55; $day++) {
            $this->payment($order, 1, \Carbon\Carbon::parse('2026-01-01')->addDays($day)->format('Y-m-d H:i:s'));
        }
        $this->order(['shop_id' => 2, 'total_price' => 250]);
        $response = $this->get(self::REPORT . '/details/1?shop_id=1')->assertOk();
        $this->assertSame(1, $response['invoices']->total());
        $this->assertCount(56, $response['invoices']->first()['rows']);
        $this->assertSame(4500, $response['invoices']->first()['rows']->last()['invoice_balance']);
        $this->assertSame(4500, $response['history']['closing']);
        $this->assertFalse($response['invoices']->hasMorePages());
    }

    public function test_history_sorts_and_paginates_whole_invoices_with_their_own_balances(): void
    {
        for ($number = 12; $number >= 1; $number--) {
            $order = $this->order([
                'invoice_number' => 'INV-' . $number,
                'order_date' => sprintf('2026-01-%02d 10:00:00', 13 - $number),
                'paid_amount' => 25,
                'payment_status' => 'Partial',
            ]);
            $this->payment($order, 25, '2026-01-31 10:00:00');
        }
        $url = self::REPORT . '/details/1?shop_id=1&from_date=2026-01-15&to_date=2026-02-01';
        $firstPage = $this->get($url)->assertOk();
        $this->assertSame(12, $firstPage['invoices']->total());
        $this->assertSame(array_map(fn ($number) => 'INV-' . $number, range(1, 10)), $firstPage['invoices']->pluck('invoice')->all());
        $firstPage->assertSeeInOrder(['>INV-1</a>', '>INV-2</a>', '>INV-10</a>'], false);
        $this->assertSame(120000, $firstPage['history']['opening']);
        $this->assertSame(90000, $firstPage['history']['closing']);

        $secondPage = $this->get($url . '&page=2')->assertOk();
        $this->assertSame(['INV-11', 'INV-12'], $secondPage['invoices']->pluck('invoice')->all());
        foreach ($secondPage['invoices'] as $invoice) {
            $this->assertCount(1, $invoice['rows']);
            $this->assertSame(10000, $invoice['opening']);
            $this->assertSame(7500, $invoice['closing']);
            $this->assertSame(7500, $invoice['rows']->first()['invoice_balance']);
        }
        $this->assertStringContainsString('shop_id=1', $secondPage['invoices']->previousPageUrl());
        $this->assertStringContainsString('from_date=2026-01-15', $secondPage['invoices']->previousPageUrl());

        // If the final page's orders settle, returning to that page still shows
        // the remaining invoices instead of an empty, inaccessible page.
        DB::table('orders')->whereIn('invoice_number', ['INV-11', 'INV-12'])
            ->update(['paid_amount' => 100, 'payment_status' => 'Paid']);
        $refreshed = $this->get($url . '&page=2')->assertOk();
        $this->assertSame(10, $refreshed['invoices']->total());
        $this->assertSame(1, $refreshed['invoices']->currentPage());
        $this->assertSame(10, $refreshed['invoices']->count());
        $refreshed->assertDontSee('>INV-11</a>', false)->assertDontSee('>INV-12</a>', false);
    }

    public function test_summary_includes_all_filtered_customers_beyond_the_first_page(): void
    {
        for ($id = 4; $id <= 30; $id++) {
            DB::table('customers')->insert(['id' => $id, 'name' => 'Customer ' . $id]);
            $this->order(['customer_id' => $id, 'paid_amount' => 25, 'payment_status' => 'Partial']);
        }
        $response = $this->get(self::REPORT)->assertOk();
        $this->assertSame(27, $response['customers']->total());
        $this->assertSame(25, $response['customers']->count());
        $this->assertEquals(2025, $response['summary']->remaining_amount);
    }

    public function test_archived_customer_history_excludes_settled_orders_and_renders_khmer(): void
    {
        $this->order(['customer_id' => 3, 'paid_amount' => 100, 'payment_status' => 'Paid']);
        $this->order(['customer_id' => 3, 'paid_amount' => 25, 'payment_status' => 'Partial']);
        $this->actingAs(new User(['name' => 'Admin', 'role' => 'super_admin', 'language_preference' => 'km']));
        $response = $this->get(self::REPORT . '/details/3')->assertOk();
        $this->assertSame(7500, $response['history']['current']);
        $this->assertSame(1, $response['invoices']->total());
        $this->assertCount(2, $response['invoices']->first()['rows']);
        $response->assertSee('ប្រវត្តិប្រតិបត្តិការទឹកប្រាក់នៅសល់');
    }

    public function test_empty_results_and_invalid_filters(): void
    {
        $response = $this->get(self::REPORT)->assertOk()->assertSee('No customers match these filters.');
        $this->assertEquals(0, $response['summary']->remaining_amount);
        $this->get(self::REPORT . '/details/1')->assertOk()->assertSee('No transactions for outstanding invoices match these filters.');
        $this->getJson(self::REPORT . '?shop_id=999')->assertUnprocessable()->assertJsonValidationErrors('shop_id');
        $this->getJson(self::REPORT . '/details/1?from_date=2026-03-01&to_date=2026-02-01')
            ->assertUnprocessable()->assertJsonValidationErrors('to_date');
        $this->getJson(self::REPORT . '/details/1?from_date=invalid&page=0')
            ->assertUnprocessable()->assertJsonValidationErrors(['from_date', 'page']);
    }

    public function test_both_pages_require_authentication_and_report_permission(): void
    {
        $this->app['auth']->forgetGuards();
        $this->get(self::REPORT)->assertRedirect();
        $this->get(self::REPORT . '/details/1')->assertRedirect();
        $this->actingAs(new User(['name' => 'No access', 'role' => 'staff']));
        $this->get(self::REPORT)->assertForbidden();
        $this->get(self::REPORT . '/details/1')->assertForbidden();
    }

    public function test_existing_sales_report_or_order_permission_grants_access(): void
    {
        $order = $this->order(['invoice_number' => 'PERMISSION-INVOICE']);
        DB::table('users')->insert(['id' => 1, 'name' => 'Report reader']);
        $user = User::findOrFail(1)->forceFill(['role' => 'staff', 'language_preference' => 'en']);
        foreach (['report-sales-view', 'order-view'] as $permission) {
            \Spatie\Permission\Models\Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }
        $user->givePermissionTo('report-sales-view');
        $this->actingAs($user);
        $this->get(self::REPORT)->assertOk();
        $this->get(self::REPORT . '/details/1')->assertOk()->assertSee('PERMISSION-INVOICE')
            ->assertDontSee('href="' . route('admin-remaining-amount-detail', $order) . '"', false);

        $user->syncPermissions(['order-view']);
        $this->get(self::REPORT)->assertOk();
        $this->get(self::REPORT . '/details/1')->assertOk()
            ->assertSee('href="' . route('admin-remaining-amount-detail', $order) . '"', false);
    }

    public function test_details_page_displays_status_field_for_each_transaction_record(): void
    {
        $order = $this->order(['paid_amount' => 30, 'payment_status' => 'Partial', 'invoice_number' => 'INV-STATUS-01']);
        $this->payment($order, 30, '2026-02-01 10:00:00');

        $response = $this->get(self::REPORT . '/details/1')->assertOk();
        $response->assertSee('Status');
        $response->assertSee('Partial');
        $response->assertSee('Paid');
    }
}
