<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PurchasePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'amount'         => ['required', 'numeric', 'min:0.01'],
            'payment_date'   => ['required', 'date'],
            'payment_method' => ['required', 'string', 'max:50'],
            'reference_no'   => ['nullable', 'string', 'max:100'],
            'receipt'        => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:5120'],
            'note'           => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Custom messages.
     */
    public function messages()
    {
        return [
            'amount.required'       => __('purchase.validation.payment_amount_required', ['default' => 'Payment amount is required.']),
            'amount.min'            => __('purchase.validation.payment_amount_min', ['default' => 'Payment amount must be greater than 0.']),
            'payment_date.required' => __('purchase.validation.payment_date_required', ['default' => 'Payment date is required.']),
            'payment_method.required' => __('purchase.validation.payment_method_required', ['default' => 'Payment method is required.']),
        ];
    }
}
