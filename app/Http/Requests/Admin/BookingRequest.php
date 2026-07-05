<?php

namespace App\Http\Requests\Admin;
use Illuminate\Support\Facades\Auth;

use Illuminate\Foundation\Http\FormRequest;

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
            'shop_id' => 'required|numeric',
            'customer_id' => 'required|numeric',
            'dataCarts' => 'required',
        ];
    }
    public function messages()
    {
        return [
            'dataCarts.required'   => "Shopping cart is required",
            'shop_id.required'   => "Shop is required",
            'shop_id.numeric'   => "Shop format invalid",
            'customer_id.required'   => "Customer is required",
            'customer_id.numeric'   => "Customer format invalid"
        ];
    }
}
