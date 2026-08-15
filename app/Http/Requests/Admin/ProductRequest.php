<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
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
            'category_id' => 'required|integer',
            'uom_id' => 'required|integer',
            'name' => [
                'required',
                'max:50',
                Rule::unique('products', 'name')->ignore($this->route('id')),
            ],
            'cost' => 'required|numeric',
            'price' => 'required|numeric',
            'status' => 'required|max:1',
        ];
    }

    public function messages()
    {
        return [
            'category_id.required' => 'Category is required',
            'uom_id.required' => 'UOM is required',
            'name.required' => 'Name is required',
            'name.max' => 'Name must not exceed 50 characters.',
            'name.unique' => 'Name already exists.',
            'cost.required' => 'Cost is required',
            'price.required' => 'Price is required',
            'cost.numeric' => 'Cost format invalid',
            'price.numeric' => 'Price format invalid',
            'status.required' => 'Status is required',
            'status.max' => 'Status must not exceed 1 characters.',
        ];
    }
}
