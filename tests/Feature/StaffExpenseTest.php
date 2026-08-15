<?php

namespace Tests\Feature;

use App\Enums\ExpenseType;
use App\Models\Staff;
use App\Models\StaffExpense;
use App\Models\User;
use App\Services\StaffExpenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffExpenseTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Staff $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->staff = Staff::create([
            'name' => 'John Doe',
            'phone_number' => '012345678',
            'status' => 1,
        ]);
    }

    /** @test */
    public function it_can_create_a_staff_expense_via_service()
    {
        $service = app(StaffExpenseService::class);

        $expense = $service->createExpense([
            'staff_id'     => $this->staff->id,
            'type'         => ExpenseType::SALARY->value,
            'amount'       => 500.00,
            'expense_date' => '2026-08-01',
            'description'  => 'Monthly Base Salary',
        ], $this->user->id);

        $this->assertDatabaseHas('staff_expenses', [
            'id'       => $expense->id,
            'staff_id' => $this->staff->id,
            'type'     => ExpenseType::SALARY->value,
            'amount'   => 500.00,
        ]);
    }

    /** @test */
    public function it_calculates_summary_totals_correctly_including_deductions()
    {
        $service = app(StaffExpenseService::class);

        $service->createExpense([
            'staff_id'     => $this->staff->id,
            'type'         => ExpenseType::SALARY->value,
            'amount'       => 1000.00,
            'expense_date' => '2026-08-01',
        ], $this->user->id);

        $service->createExpense([
            'staff_id'     => $this->staff->id,
            'type'         => ExpenseType::BONUS->value,
            'amount'       => 200.00,
            'expense_date' => '2026-08-02',
        ], $this->user->id);

        $service->createExpense([
            'staff_id'     => $this->staff->id,
            'type'         => ExpenseType::DEDUCTION->value,
            'amount'       => 150.00,
            'expense_date' => '2026-08-03',
        ], $this->user->id);

        $summary = $service->calculateSummary(['staff_id' => $this->staff->id]);

        $this->assertEquals(1200.00, $summary['grossAdditions']);
        $this->assertEquals(150.00, $summary['deduction']);
        $this->assertEquals(1050.00, $summary['netTotal']);
    }

    /** @test */
    public function it_supports_soft_deletes_and_restoration()
    {
        $service = app(StaffExpenseService::class);

        $expense = $service->createExpense([
            'staff_id'     => $this->staff->id,
            'type'         => ExpenseType::OTHER->value,
            'amount'       => 75.00,
            'expense_date' => '2026-08-01',
        ], $this->user->id);

        $service->softDeleteExpense($expense);

        $this->assertSoftDeleted('staff_expenses', ['id' => $expense->id]);

        $service->restoreExpense($expense->id);

        $this->assertDatabaseHas('staff_expenses', [
            'id'         => $expense->id,
            'deleted_at' => null,
        ]);
    }
}
