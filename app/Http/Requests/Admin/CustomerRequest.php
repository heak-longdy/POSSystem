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
            'name.required'        => 'Please enter the customer name.',
            'name.max'             => 'The name may not be greater than 255 characters.',
            'password.required'    => 'Password is required.',
            'password.min'         => 'Password must be at least 6 characters.',
            'password.confirmed'   => 'Password confirmation does not match.',
            'status.required'      => 'Status is required.',
            'status.boolean'       => 'Status must be true or false.',
            // Add more messages as needed
        ];
    }
}
