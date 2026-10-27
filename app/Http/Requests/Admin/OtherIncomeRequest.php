<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class OtherIncomeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'category_id'              => 'required|exists:other_income_categories,id',
            'supplier_id'              => 'nullable|exists:suppliers,id',
            'shop_id'                  => 'nullable|exists:shops,id',
            'title'                    => 'required|string|max:255',
            'income_date'              => 'required|date',
            'received_amount'          => 'required|numeric|min:' . ($this->route('id') ? '0' : '0.01'),
            'reference_no'             => 'nullable|string|max:100',
            'description'              => 'nullable|string|max:2000',
            'status'                   => 'required|integer',
            'attachment'               => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx',

            // Payment details for the amount received when creating income.
            'payment_method'           => 'nullable|string|max:50',
            'payment_date'             => 'nullable|date',
            'payment_reference_no'     => 'nullable|string|max:100',
            'payment_receipt'          => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,webp',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required'     => __('other_income.validation.category_id_required'),
            'category_id.exists'       => __('other_income.validation.category_id_required'),
            'title.required'           => __('other_income.validation.title_required'),
            'income_date.required'     => __('other_income.validation.income_date_required'),
            'received_amount.required' => __('other_income.validation.received_amount_required'),
            'received_amount.numeric'  => __('other_income.validation.received_amount_numeric'),
            'received_amount.min'      => __('other_income.validation.received_amount_min'),
        ];
    }
}
