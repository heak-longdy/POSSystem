<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ShopRequest extends FormRequest
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
            'name' => [
                'required',
                'max:50',
                Rule::unique('shops', 'name')->ignore($this->route('id')),
            ],
            'phone' => 'required|max:20',
            'address' => 'required|string',
            'status' => 'required|max:1',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => __('shop.validation.name_required'),
            'name.max' => __('shop.validation.name_max'),
            'name.unique' => __('shop.validation.name_unique'),
            'phone.required' => __('shop.validation.phone_required'),
            'phone.max' => __('shop.validation.phone_max'),
            'address.required' => __('shop.validation.address_required'),
            'status.required' => __('shop.validation.status_required'),
            'status.max' => __('shop.validation.status_max'),
        ];
    }
}
