<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class OrderRequest extends FormRequest
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
            'id' => 'nullable|integer|exists:orders,id',
            'shop_id' => [
                'required',
                Rule::exists('shops', 'id')->where('status', 1),
            ],
            'barber_id' => [
                'nullable',
                Rule::exists('barbers', 'id')->where('status', 1),
            ],
            'customer_id' => [
                'required',
                Rule::exists('customers', 'id')->where('status', 1),
            ],
            'order_date' => 'required|date',
            'delivery_date' => 'nullable|date',
            'dataCarts' => 'required|json',
            'partial_payment_amount' => 'nullable|numeric|min:0',
            'partial_payment_note' => 'nullable|string|max:500',
        ];
    }

    public function messages()
    {
        return [
            'dataCarts.required'   => __('order.validation.cart_required'),
            'dataCarts.json'       => __('order.validation.cart_invalid'),
            'shop_id.required'     => __('order.validation.shop_required'),
            'shop_id.exists'       => __('order.validation.shop_invalid'),
            'barber_id.exists'     => __('order.validation.barber_invalid'),
            'customer_id.required' => __('order.validation.customer_required'),
            'customer_id.exists'   => __('order.validation.customer_invalid'),
            'order_date.required'  => __('order.validation.order_date_required'),
            'order_date.date'      => __('order.validation.order_date_invalid'),
            'partial_payment_amount.numeric' => __('order.validation.partial_numeric'),
            'partial_payment_amount.min'     => __('order.validation.partial_min'),
        ];
    }
}
