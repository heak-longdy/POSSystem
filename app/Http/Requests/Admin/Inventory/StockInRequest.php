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
            'supplier_id.required' => __('stock_in.validation.supplier_required'),
            'supplier_id.exists'   => __('stock_in.validation.supplier_invalid'),
            'shop_id.required'     => __('stock_in.validation.shop_required'),
            'shop_id.exists'       => __('stock_in.validation.shop_invalid'),
            'product_id.required'  => __('stock_in.validation.product_required'),
            'product_id.exists'    => __('stock_in.validation.product_invalid'),
            'qty.required'         => __('stock_in.validation.qty_required'),
            'qty.integer'          => __('stock_in.validation.qty_integer'),
            'qty.min'              => __('stock_in.validation.qty_min'),
            'remark.max'           => __('stock_in.validation.remark_max'),
        ];
    }
}
