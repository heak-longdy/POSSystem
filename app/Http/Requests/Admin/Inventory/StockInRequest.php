<?php

namespace App\Http\Requests\Admin\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StockInRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    public function rules()
    {
        return [
            'supplier_id' => [
                'required',
                Rule::exists('suppliers', 'id')->where('status', 1),
            ],
            'shop_id' => [
                'required',
                Rule::exists('shops', 'id')->where('status', 1),
            ],
            'product_id' => [
                'required',
                Rule::exists('products', 'id')->where('status', 1),
            ],
            'qty' => 'required|integer|min:1',
            'remark' => 'nullable|string|max:1000',
            'save_opt' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'supplier_id.required' => 'Supplier is required',
            'supplier_id.exists' => 'Supplier is invalid',
            'shop_id.required' => 'Shop is required',
            'shop_id.exists' => 'Shop is invalid',
            'product_id.required' => 'Product is required',
            'product_id.exists' => 'Product is invalid',
            'qty.required' => 'Qty is required',
            'qty.integer' => 'Qty format invalid',
            'qty.min' => 'Qty must be at least 1',
            'remark.max' => 'Remark must not exceed 1000 characters.',
        ];
    }
}
