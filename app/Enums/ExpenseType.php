<?php

namespace App\Enums;

enum ExpenseType: string
{
    case SALARY    = 'Salary';
    case BONUS     = 'Bonus';
    case DEDUCTION = 'Deduction';
    case OTHER     = 'Other';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match($this) {
            self::SALARY    => 'Salary',
            self::BONUS     => 'Bonus',
            self::DEDUCTION => 'Deduction',
            self::OTHER     => 'Other Expense',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::SALARY    => 'badge bg-primary',
            self::BONUS     => 'badge bg-success',
            self::DEDUCTION => 'badge bg-danger',
            self::OTHER     => 'badge bg-secondary',
        };
    }

    public function isDeduction(): bool
    {
        return $this === self::DEDUCTION;
    }
}
