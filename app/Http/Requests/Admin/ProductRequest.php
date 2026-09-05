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
            'category_id.required' => __('product.validation.category_required'),
            'uom_id.required' => __('product.validation.uom_required'),
            'name.required' => __('product.validation.name_required'),
            'name.max' => __('product.validation.name_max'),
            'name.unique' => __('product.validation.name_unique'),
            'cost.required' => __('product.validation.cost_required'),
            'price.required' => __('product.validation.price_required'),
            'cost.numeric' => __('product.validation.cost_numeric'),
            'price.numeric' => __('product.validation.price_numeric'),
            'status.required' => __('product.validation.status_required'),
            'status.max' => __('product.validation.status_max'),
        ];
    }
}
