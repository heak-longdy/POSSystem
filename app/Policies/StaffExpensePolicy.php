<?php

namespace App\Policies;

use App\Models\StaffExpense;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StaffExpensePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('staff-expense-view');
    }

    public function view(User $user, StaffExpense $expense): bool
    {
        return $user->can('staff-expense-view');
    }

    public function create(User $user): bool
    {
        return $user->can('staff-expense-create');
    }

    public function update(User $user, StaffExpense $expense): bool
    {
        return $user->can('staff-expense-update');
    }

    public function delete(User $user, StaffExpense $expense): bool
    {
        return $user->can('staff-expense-delete');
    }

    public function restore(User $user, StaffExpense $expense): bool
    {
        return $user->can('staff-expense-restore');
    }

    public function forceDelete(User $user, StaffExpense $expense): bool
    {
        return $user->can('staff-expense-force-delete');
    }
}
