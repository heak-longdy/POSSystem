<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class OtherIncomePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'amount'         => 'required|numeric|min:0.01',
            'payment_date'   => 'required|date',
            'payment_method' => 'required|string|max:50',
            'reference_no'   => 'nullable|string|max:100',
            'receipt'        => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,webp',
            'note'           => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => __('other_income.validation.payment_amount_min'),
            'amount.min'      => __('other_income.validation.payment_amount_min'),
        ];
    }
}
