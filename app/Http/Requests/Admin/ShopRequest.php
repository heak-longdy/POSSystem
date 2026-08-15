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
                Rule::unique('products', 'name')->ignore($this->route('id')),
            ],
            'phone' => 'required|max:20',
            'address' => 'required|string',
            'status' => 'required|max:1',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Name is required',
            'name.max' => 'Name must not exceed 50 characters.',
            'name.unique' => 'Name already exists.',
            'phone.required' => 'Phone is required',
            'phone.max' => 'Phone must not exceed 20 characters.',
            'address.required' => 'Address is required',
            'status.required' => 'Status is required',
            'status.max' => 'Status must not exceed 1 characters.',
        ];
    }
}
