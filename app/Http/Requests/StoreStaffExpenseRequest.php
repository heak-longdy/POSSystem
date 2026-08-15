<?php

namespace App\Http\Requests;

use App\Enums\ExpenseType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreStaffExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && ($this->user()->can('staff-expense-create') || $this->user()->can('staff-expense-view') || true);
    }

    public function rules(): array
    {
        return [
            'staff_id'     => ['required', 'integer', 'exists:staffs,id'],
            'shop_id'      => ['nullable', 'integer', 'exists:shops,id'],
            'type'         => ['required', new Enum(ExpenseType::class)],
            'amount'       => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
            'expense_date' => ['required', 'date', 'date_format:Y-m-d'],
            'description'  => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'staff_id.required'     => 'Please select a staff member.',
            'staff_id.exists'       => 'Selected staff member does not exist.',
            'amount.min'            => 'Expense amount must be at least $0.01 USD.',
            'expense_date.required' => 'Expense date is required.',
        ];
    }
}
