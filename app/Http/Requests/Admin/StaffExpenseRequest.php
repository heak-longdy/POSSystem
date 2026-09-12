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
            'staff_id.required'     => __('staff_expense.validation.staff_id_required'),
            'staff_id.exists'       => __('staff_expense.validation.staff_id_exists'),
            'type.required'         => __('staff_expense.validation.type_required'),
            'amount.required'       => __('staff_expense.validation.amount_required'),
            'amount.numeric'        => __('staff_expense.validation.amount_numeric'),
            'amount.min'            => __('staff_expense.validation.amount_min'),
            'expense_date.required' => __('staff_expense.validation.expense_date_required'),
            'status.required'       => __('staff_expense.validation.status_required'),
            'description.max'       => __('staff_expense.validation.description_max'),
        ];
    }
}
