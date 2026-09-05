<?php

namespace App\Http\Requests\Admin;
use Illuminate\Support\Facades\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookingRequest extends FormRequest
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
            'id' => 'nullable|integer|exists:bookings,id',
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
            'booking_date' => 'required|date',
            'dataCarts' => 'required|json',
            'partial_payment_amount' => 'nullable|numeric|min:0',
        ];
    }
    public function messages()
    {
        return [
            'dataCarts.required'   => __('booking.validation.cart_required'),
            'dataCarts.json'   => __('booking.validation.cart_invalid'),
            'shop_id.required'   => __('booking.validation.shop_required'),
            'shop_id.exists'   => __('booking.validation.shop_invalid'),
            'barber_id.exists'   => __('booking.validation.barber_invalid'),
            'customer_id.required'   => __('booking.validation.customer_required'),
            'customer_id.exists'   => __('booking.validation.customer_invalid'),
            'booking_date.required' => __('booking.validation.booking_date_required'),
            'booking_date.date' => __('booking.validation.booking_date_invalid'),
            'partial_payment_amount.numeric' => __('booking.validation.partial_numeric'),
            'partial_payment_amount.min' => __('booking.validation.partial_min'),
        ];
    }
}
