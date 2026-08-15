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
            'dataCarts.required'   => "Shopping cart is required",
            'dataCarts.json'   => "Shopping cart format invalid",
            'shop_id.required'   => "Shop is required",
            'shop_id.exists'   => "Shop is invalid",
            'barber_id.exists'   => "Barber is invalid",
            'customer_id.required'   => "Customer is required",
            'customer_id.exists'   => "Customer is invalid",
            'booking_date.required' => "Booking date is required",
            'booking_date.date' => "Booking date format invalid",
            'partial_payment_amount.numeric' => "Partial payment amount must be numeric",
            'partial_payment_amount.min' => "Partial payment amount must be at least 0",
        ];
    }
}
