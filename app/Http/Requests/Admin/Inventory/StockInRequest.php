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
        $rules = [
            'supplier_id' => [
                'required',
                Rule::exists('suppliers', 'id')->where('status', 1),
            ],
            'shop_id' => [
                'required',
                Rule::exists('shops', 'id')->where('status', 1),
            ],
            'remark' => 'nullable|string|max:1000',
            'save_opt' => 'nullable|string',
        ];

        if ($this->has('items')) {
            $rules['items'] = 'required|array|min:1';
            $rules['items.*.product_id'] = [
                'required',
                Rule::exists('products', 'id')->where('status', 1),
            ];
            $rules['items.*.qty'] = 'required|integer|min:1';
            $rules['items.*.remark'] = 'nullable|string|max:1000';
        } else {
            $rules['product_id'] = [
                'required',
                Rule::exists('products', 'id')->where('status', 1),
            ];
            $rules['qty'] = 'required|integer|min:1';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'supplier_id.required'        => __('stock_in.validation.supplier_required'),
            'supplier_id.exists'          => __('stock_in.validation.supplier_invalid'),
            'shop_id.required'            => __('stock_in.validation.shop_required'),
            'shop_id.exists'              => __('stock_in.validation.shop_invalid'),
            'product_id.required'         => __('stock_in.validation.product_required'),
            'product_id.exists'           => __('stock_in.validation.product_invalid'),
            'qty.required'                => __('stock_in.validation.qty_required'),
            'qty.integer'                 => __('stock_in.validation.qty_integer'),
            'qty.min'                     => __('stock_in.validation.qty_min'),
            'items.required'              => __('stock_in.validation.items_required'),
            'items.min'                   => __('stock_in.validation.items_min'),
            'items.*.product_id.required' => __('stock_in.validation.product_required'),
            'items.*.product_id.exists'   => __('stock_in.validation.product_invalid'),
            'items.*.qty.required'        => __('stock_in.validation.qty_required'),
            'items.*.qty.integer'         => __('stock_in.validation.qty_integer'),
            'items.*.qty.min'             => __('stock_in.validation.qty_min'),
            'items.*.remark.max'          => __('stock_in.validation.remark_max'),
            'remark.max'                  => __('stock_in.validation.remark_max'),
        ];
    }
}
