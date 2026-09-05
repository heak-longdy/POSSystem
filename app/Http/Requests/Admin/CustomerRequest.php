<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CustomerRequest extends FormRequest
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
            'name'       => 'required|string|max:255',
            // 'ordering'   => 'nullable|integer',
            // 'phone'      => 'nullable|string|max:20',
            // 'address'    => 'nullable|string|max:255',
            // 'profile'    => 'nullable|string|max:255',
            // 'password'   => 'required|string|min:6|confirmed',
            // 'status'     => 'required|boolean',
            // 'total_point'=> 'nullable|integer|min:0',
        ];
    }
    public function messages()
    {
        return [
            'name.required'        => __('customer.validation.name_required'),
            'name.max'             => __('customer.validation.name_max'),
            'password.required'    => __('customer.validation.password_required'),
            'password.min'         => __('customer.validation.password_min'),
            'password.confirmed'   => __('customer.validation.password_confirmed'),
            'status.required'      => __('customer.validation.status_required'),
            'status.boolean'       => __('customer.validation.status_boolean'),
            // Add more messages as needed
        ];
    }
}
