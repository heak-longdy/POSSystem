<?php

namespace App\Services;

use App\Enums\ExpenseType;
use App\Models\StaffExpense;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class StaffExpenseService
{
    /**
     * Get paginated expenses with filters applied.
     */
    public function getFilteredExpenses(array $filters, int $perPage = 50, bool $onlyTrashed = false): LengthAwarePaginator
    {
        $query = StaffExpense::with(['staff', 'shop', 'createdBy']);

        if ($onlyTrashed) {
            $query->onlyTrashed();
        }

        return $query
            ->dateBetween($filters['from_date'] ?? null, $filters['to_date'] ?? null)
            ->ofStaff($filters['staff_id'] ?? null)
            ->ofType($filters['type'] ?? null)
            ->ofShop($filters['shop_id'] ?? null)
            ->orderBy('expense_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    /**
     * Calculate aggregate summaries for filtered expenses.
     */
    public function calculateSummary(array $filters, bool $onlyTrashed = false): array
    {
        $query = StaffExpense::query();

        if ($onlyTrashed) {
            $query->onlyTrashed();
        }

        $rawTotals = $query
            ->dateBetween($filters['from_date'] ?? null, $filters['to_date'] ?? null)
            ->ofStaff($filters['staff_id'] ?? null)
            ->ofType($filters['type'] ?? null)
            ->ofShop($filters['shop_id'] ?? null)
            ->select('type', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('type')
            ->pluck('total_amount', 'type')
            ->toArray();

        $salary    = (float) ($rawTotals[ExpenseType::SALARY->value] ?? 0);
        $bonus     = (float) ($rawTotals[ExpenseType::BONUS->value] ?? 0);
        $deduction = (float) ($rawTotals[ExpenseType::DEDUCTION->value] ?? 0);
        $other     = (float) ($rawTotals[ExpenseType::OTHER->value] ?? 0);

        $grossAdditions = $salary + $bonus + $other;
        $netTotal       = $grossAdditions - $deduction;

        return [
            'salary'         => $salary,
            'bonus'          => $bonus,
            'deduction'      => $deduction,
            'other'          => $other,
            'grossAdditions' => $grossAdditions,
            'netTotal'       => $netTotal,
        ];
    }

    /**
     * Create a new expense record in transaction.
     */
    public function createExpense(array $data, int $userId): StaffExpense
    {
        return DB::transaction(function () use ($data, $userId) {
            $data['created_by'] = $userId;
            return StaffExpense::create($data);
        });
    }

    /**
     * Update existing expense record.
     */
    public function updateExpense(StaffExpense $expense, array $data): StaffExpense
    {
        return DB::transaction(function () use ($expense, $data) {
            $expense->update($data);
            return $expense->fresh();
        });
    }

    /**
     * Soft delete an expense.
     */
    public function softDeleteExpense(StaffExpense $expense): bool
    {
        return DB::transaction(fn () => $expense->delete());
    }

    /**
     * Restore soft deleted expense.
     */
    public function restoreExpense(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $expense = StaffExpense::onlyTrashed()->findOrFail($id);
            return $expense->restore();
        });
    }

    /**
     * Force delete expense permanently.
     */
    public function forceDeleteExpense(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $expense = StaffExpense::onlyTrashed()->findOrFail($id);
            return $expense->forceDelete();
        });
    }
}
