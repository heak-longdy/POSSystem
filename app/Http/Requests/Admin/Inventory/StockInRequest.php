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
            'order_date'             => 'nullable|date',
            'expected_delivery_date' => 'nullable|date',
            'discount_amount'        => 'nullable|numeric|min:0',
            'tax_amount'             => 'nullable|numeric|min:0',
            'shipping_cost'          => 'nullable|numeric|min:0',
            'reference_no'           => 'nullable|string|max:100',
            'remark'                 => 'nullable|string|max:2000',
            'save_opt'               => 'nullable|string',
            'fulfillment_type'       => 'nullable|string|in:standard,direct',
            'immediate_stock_in'     => 'nullable|boolean',
            'initial_payment_amount' => 'nullable|numeric|min:0',
            'initial_payment_method' => 'nullable|string|max:50',
            'payment_date'           => 'nullable|date',
            'payment_reference_no'   => 'nullable|string|max:100',
            'payment_note'           => 'nullable|string|max:1000',
            'payment_receipt'        => 'nullable|file|mimes:jpeg,png,jpg,pdf,webp|max:10240',
        ];

        if ($this->has('items') && is_array($this->items)) {
            $rules['items'] = 'required|array|min:1';
            $rules['items.*.product_id'] = [
                'required',
                Rule::exists('products', 'id')->where('status', 1),
            ];
            $rules['items.*.qty'] = 'required|numeric|min:0.01';
            $rules['items.*.unit_cost'] = 'nullable|numeric|min:0';
            $rules['items.*.remark'] = 'nullable|string|max:1000';
        } else {
            $rules['product_id'] = [
                'required',
                Rule::exists('products', 'id')->where('status', 1),
            ];
            $rules['qty'] = 'required|numeric|min:0.01';
            $rules['unit_cost'] = 'nullable|numeric|min:0';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'supplier_id.required'        => __('stock_in.validation.supplier_required') ?? 'Supplier is required',
            'supplier_id.exists'          => __('stock_in.validation.supplier_invalid') ?? 'Selected supplier is invalid',
            'shop_id.required'            => __('stock_in.validation.shop_required') ?? 'Shop is required',
            'shop_id.exists'              => __('stock_in.validation.shop_invalid') ?? 'Selected shop is invalid',
            'product_id.required'         => __('stock_in.validation.product_required') ?? 'Product is required',
            'product_id.exists'           => __('stock_in.validation.product_invalid') ?? 'Selected product is invalid',
            'qty.required'                => __('stock_in.validation.qty_required') ?? 'Quantity is required',
            'qty.numeric'                 => __('stock_in.validation.qty_integer') ?? 'Quantity must be a valid number',
            'qty.min'                     => __('stock_in.validation.qty_min') ?? 'Quantity must be at least 0.01',
            'items.required'              => __('stock_in.validation.items_required') ?? 'At least one item is required',
            'items.min'                   => __('stock_in.validation.items_min') ?? 'At least one item is required',
            'items.*.product_id.required' => __('stock_in.validation.product_required') ?? 'Product is required',
            'items.*.product_id.exists'   => __('stock_in.validation.product_invalid') ?? 'Selected product is invalid',
            'items.*.qty.required'        => __('stock_in.validation.qty_required') ?? 'Quantity is required',
            'items.*.qty.numeric'         => __('stock_in.validation.qty_integer') ?? 'Quantity must be a valid number',
            'items.*.qty.min'             => __('stock_in.validation.qty_min') ?? 'Quantity must be at least 0.01',
            'items.*.remark.max'          => __('stock_in.validation.remark_max') ?? 'Item remark is too long',
            'remark.max'                  => __('stock_in.validation.remark_max') ?? 'General remark is too long',
        ];
    }
}
