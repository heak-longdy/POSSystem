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
            'phone'      => 'required|string|max:50',
            'address'    => 'nullable|string|max:500',
            'profile'    => 'nullable|string|max:255',
            'status'     => 'required|in:1,2',
        ];
    }
    public function messages()
    {
        return [
            'name.required'        => __('customer.validation.name_required'),
            'name.max'             => __('customer.validation.name_max'),
            'phone.required'       => __('customer.validation.phone_required'),
            'phone.max'            => __('customer.validation.phone_max'),
            'status.required'      => __('customer.validation.status_required'),
            'status.in'            => __('customer.validation.status_invalid'),
        ];
    }
}
