<?php

namespace Tests\Feature;

use App\Models\OtherIncome;
use App\Models\OtherIncomeCategory;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OtherIncomeCreationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        require_once database_path('migrations/2026_08_02_231335_create_permission_tables.php');
        (new \CreatePermissionTables())->up();

        foreach (['users', 'suppliers', 'shops'] as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->integer('status')->default(1);
                $table->softDeletes();
            });
            DB::table($name)->insert(['id' => 1, 'name' => 'Income test']);
        }

        foreach ([
            '2026_10_26_000001_create_other_income_categories_table.php',
            '2026_10_26_000002_create_other_incomes_table.php',
            '2026_10_26_000003_create_other_income_payments_table.php',
        ] as $file) {
            (require database_path('migrations/' . $file))->up();
        }
        OtherIncomeCategory::create(['name' => 'Other income', 'code' => 'OTHER']);

        $user = new User(['name' => 'Income admin', 'role' => 'super_admin', 'language_preference' => 'en']);
        $user->id = 1;
        $this->actingAs($user);
    }

    public function test_create_form_renders_without_an_existing_income_in_both_languages(): void
    {
        foreach (['en', 'km'] as $locale) {
            $this->app['auth']->user()->language_preference = $locale;
            $response = $this->withSession(['locale' => $locale])
                ->get(route('admin-other-income-create', ['supplier_id' => 1]))
                ->assertOk()
                ->assertViewIs('admin::pages.otherIncome.store')
                ->assertSee('<html lang="' . $locale . '">', false)
                ->assertSee(__('other_income.form.received_amount'))
                ->assertDontSee('name="expected_amount"', false)
                ->assertDontSee('name="initial_payment_amount"', false)
                ->assertDontSee('name="attachment"', false)
                ->assertDontSee('name="reference_no"', false);

            $this->assertInputValue($response->getContent(), 'calculation_period_start', '');
            $this->assertInputValue($response->getContent(), 'calculation_period_end', '');
            $this->assertInputValue($response->getContent(), 'income_date', date('Y-m-d'));
            $this->assertInputValue($response->getContent(), 'received_amount', '');
            $this->assertSame(1, $response->viewData('selectedSupplier')->id);
        }
    }

    public function test_validation_failure_can_reopen_create_form_with_old_input(): void
    {
        $url = route('admin-other-income-create');
        $this->from($url)->post(route('admin-other-income-save'), [
            'title' => 'Supplier incentive',
            'income_date' => '2026-10-26',
            'received_amount' => 100,
            'calculation_period_start' => '2026-07-01',
            'calculation_period_end' => '2026-09-30',
            'status' => 1,
        ])->assertRedirect($url)->assertSessionHasErrors('category_id');

        $response = $this->get($url)->assertOk();
        $this->assertInputValue($response->getContent(), 'title', 'Supplier incentive');
        $this->assertInputValue($response->getContent(), 'received_amount', '100');
        $this->assertInputValue($response->getContent(), 'calculation_period_start', '2026-07-01');
        $this->assertInputValue($response->getContent(), 'calculation_period_end', '2026-09-30');
    }

    public function test_save_and_new_creates_income_with_payment_and_reopens_form(): void
    {
        $this->post(route('admin-other-income-save'), [
            'category_id' => 1,
            'supplier_id' => 1,
            'title' => 'Supplier incentive',
            'income_date' => '2026-10-26',
            'received_amount' => 125.50,
            'payment_method' => 'ABA Pay',
            'calculation_period_start' => '2026-07-01',
            'calculation_period_end' => '2026-09-30',
            'status' => 1,
            'save_opt' => 'save_new',
        ])->assertRedirect(route('admin-other-income-create'))
            ->assertSessionHasNoErrors()
            ->assertSessionMissing('error');

        $income = OtherIncome::sole();
        $this->assertSame('125.50', $income->expected_amount);
        $this->assertSame('125.50', $income->received_amount);
        $this->assertSame('0.00', $income->remaining_amount);
        $this->assertSame('paid', $income->payment_status);
        $this->assertSame(1, $income->payments()->count());
        $this->assertSame('125.50', $income->payments()->first()->amount);
        $this->assertSame('ABA Pay', $income->payments()->first()->payment_method);
        $this->assertSame('2026-10-26', $income->payments()->first()->payment_date->format('Y-m-d'));

        $this->get(route('admin-other-income-create'))->assertOk();

        $response = $this->get(route('admin-other-income-edit', $income->id))->assertOk();
        $this->assertInputValue($response->getContent(), 'received_amount', '125.50');
        $this->assertInputValue($response->getContent(), 'title', 'Supplier incentive');
        $this->assertInputValue($response->getContent(), 'calculation_period_start', '2026-07-01');
        $this->assertInputValue($response->getContent(), 'calculation_period_end', '2026-09-30');
    }

    public function test_received_amount_edits_reconcile_payments_without_rewriting_receipts(): void
    {
        $this->post(route('admin-other-income-save'), $this->incomePayload(125.50))
            ->assertRedirect(route('admin-other-income-list', 1))->assertSessionMissing('error');
        $income = OtherIncome::sole();
        $receipt = $income->payments()->first();

        foreach ([150.75, 90.25, 0, 65.50] as $amount) {
            $this->post(route('admin-other-income-save', $income->id), $this->incomePayload($amount))
                ->assertRedirect(route('admin-other-income-list', 1))->assertSessionMissing('error');
            $income->refresh();
            $this->assertSame(number_format($amount, 2, '.', ''), $income->received_amount);
            $this->assertSame($income->received_amount, $income->expected_amount);
            $this->assertSame('0.00', $income->remaining_amount);
            $this->assertSame($amount > 0 ? 'paid' : 'pending', $income->payment_status);
            $this->assertEqualsWithDelta($amount, $income->payments()->sum('amount'), 0.001);
            $this->assertSame('125.50', $receipt->fresh()->amount);
        }
        $this->assertSame(5, $income->payments()->count());
        $this->assertSame(2, $income->payments()->where('amount', '<', 0)->count());
        $this->assertSame(4, $income->payments()->where('payment_method', 'Adjustment')->count());

        // Re-saving the same amount must not create another payment.
        $this->post(route('admin-other-income-save', $income->id), $this->incomePayload(65.50))
            ->assertRedirect(route('admin-other-income-list', 1))->assertSessionMissing('error');
        $this->assertSame(5, $income->payments()->count());
        $this->assertInputValue($this->get(route('admin-other-income-edit', $income->id))->assertOk()->getContent(), 'received_amount', '65.50');

        $this->postJson(route('admin-other-income-save', $income->id), $this->incomePayload(-1))
            ->assertUnprocessable()->assertJsonValidationErrors('received_amount');
        $this->assertSame('65.50', $income->fresh()->received_amount);
        $this->assertSame(5, $income->payments()->count());
    }

    public function test_editing_older_partial_income_keeps_its_receivable_and_existing_document(): void
    {
        $income = OtherIncome::create([
            'category_id' => 1, 'title' => 'Older income', 'income_date' => '2026-10-01',
            'expected_amount' => 200, 'received_amount' => 30,
            'attachment_path' => 'uploads/other_income/documents/agreement.pdf',
            'attachment_name' => 'agreement.pdf',
            'reference_no' => 'AGR-EXISTING',
        ]);
        $income->payments()->create([
            'payment_number' => 'LEGACY-1', 'amount' => 30, 'payment_date' => '2026-10-01',
            'payment_method' => 'Cash', 'created_by' => 1,
        ]);
        $this->post(route('admin-other-income-save', $income->id), $this->incomePayload(60))
            ->assertRedirect(route('admin-other-income-list', 1))->assertSessionMissing('error');
        $income->refresh();
        $this->assertSame('200.00', $income->expected_amount);
        $this->assertSame('60.00', $income->received_amount);
        $this->assertSame('140.00', $income->remaining_amount);
        $this->assertSame('partial', $income->payment_status);
        $this->assertSame('uploads/other_income/documents/agreement.pdf', $income->attachment_path);
        $this->assertSame('AGR-EXISTING', $income->reference_no);
        $this->assertEquals(60, $income->payments()->sum('amount'));
    }

    public function test_new_income_requires_a_positive_received_amount(): void
    {
        foreach ([null, '', 'not a number', -1, 0] as $amount) {
            $this->postJson(route('admin-other-income-save'), $this->incomePayload($amount))
                ->assertUnprocessable()->assertJsonValidationErrors('received_amount');
        }
        $this->assertDatabaseCount('other_incomes', 0);
        $this->assertDatabaseCount('other_income_payments', 0);
    }

    private function incomePayload($amount): array
    {
        return [
            'category_id' => 1, 'title' => 'Supplier incentive', 'income_date' => '2026-10-26',
            'received_amount' => $amount, 'payment_method' => 'Cash', 'status' => 1,
        ];
    }

    private function assertInputValue(string $html, string $name, string $expected): void
    {
        $document = new \DOMDocument();
        @$document->loadHTML('<?xml encoding="UTF-8">' . $html);
        $input = (new \DOMXPath($document))->query('//input[@name="' . $name . '"]')->item(0);
        $this->assertNotNull($input, 'Missing input: ' . $name);
        $this->assertSame($expected, $input->getAttribute('value'), $name);
    }
}
