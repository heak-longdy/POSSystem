<?php

namespace App\Http\Requests\Admin;

use App\Enums\ExpenseType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;

class StaffExpenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'staff_id'     => 'required|exists:staffs,id',
            'shop_id'      => 'nullable|exists:shops,id',
            'type'         => ['required', new Enum(ExpenseType::class)],
            'amount'       => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'description'  => 'nullable|string|max:1000',
            'status'       => 'required',
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'staff_id.required'     => 'Please select a staff member.',
            'staff_id.exists'       => 'Selected staff member does not exist.',
            'type.required'         => 'Expense type is required.',
            'amount.required'       => 'Expense amount is required.',
            'amount.min'            => 'Expense amount must be at least $0.01.',
            'expense_date.required' => 'Expense date is required.',
            'status.required'       => 'Status is required.',
        ];
    }
}
