<?php

namespace App\Http\Requests\Admin;

use App\Models\ShopProduct;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ShopProductRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    public function rules()
    {
        return [
            'products' => 'required|array|min:1',
            'products.*.id' => 'nullable|integer',
            'products.*.product_id' => 'required|integer|exists:products,id|distinct',
            'products.*.product_name' => 'nullable|string',
            'products.*.price' => 'required|numeric|min:0',
            'products.*.point' => 'nullable|numeric|min:0',
            'products.*.max_qty' => 'nullable|integer|min:0',
            'products.*.commission' => 'nullable|numeric|min:0',
            'products.*.commission_type' => 'nullable|in:usd,khr,percent',
            'products.*.status' => 'required|in:1,2',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $shopId = $this->route('id');

            foreach ($this->input('products', []) as $index => $product) {
                $shopProductId = $product['id'] ?? null;
                $productId = $product['product_id'] ?? null;

                if (!$productId) {
                    continue;
                }

                if (
                    isset($product['commission']) &&
                    $product['commission'] !== '' &&
                    empty($product['commission_type'])
                ) {
                    $validator->errors()->add("products.{$index}.commission_type", __('shop.validation.commission_type_required'));
                }

                if ($shopProductId) {
                    $shopProduct = ShopProduct::where('id', $shopProductId)
                        ->where('shop_id', $shopId)
                        ->first();

                    if (!$shopProduct) {
                        $validator->errors()->add("products.{$index}.product_id", __('shop.validation.shop_product_not_belong'));
                    } elseif ((int) $shopProduct->product_id !== (int) $productId) {
                        $validator->errors()->add("products.{$index}.product_id", __('shop.validation.cannot_change_product'));
                    }
                }

                $exists = ShopProduct::withTrashed()
                    ->where('shop_id', $shopId)
                    ->where('product_id', $productId)
                    ->when($shopProductId, function ($query) use ($shopProductId) {
                        $query->where('id', '!=', $shopProductId);
                    })
                    ->exists();

                if ($exists) {
                    $validator->errors()->add("products.{$index}.product_id", __('shop.validation.product_already_exists'));
                }
            }
        });
    }

    public function messages()
    {
        return [
            'products.required' => __('shop.validation.products_required'),
            'products.array' => __('shop.validation.products_array'),
            'products.min' => __('shop.validation.products_required'),
            'products.*.product_id.required' => __('shop.validation.product_id_required'),
            'products.*.product_id.exists' => __('shop.validation.product_id_exists'),
            'products.*.product_id.distinct' => __('shop.validation.product_id_distinct'),
            'products.*.price.required' => __('shop.validation.price_required'),
            'products.*.price.numeric' => __('shop.validation.price_numeric'),
            'products.*.price.min' => __('shop.validation.price_min'),
            'products.*.point.numeric' => __('shop.validation.point_numeric'),
            'products.*.point.min' => __('shop.validation.point_min'),
            'products.*.max_qty.integer' => __('shop.validation.max_qty_integer'),
            'products.*.max_qty.min' => __('shop.validation.max_qty_min'),
            'products.*.commission.numeric' => __('shop.validation.commission_numeric'),
            'products.*.commission.min' => __('shop.validation.commission_min'),
            'products.*.commission_type.required_with' => __('shop.validation.commission_type_required'),
            'products.*.commission_type.in' => __('shop.validation.commission_type_invalid'),
            'products.*.status.required' => __('shop.validation.status_required'),
            'products.*.status.in' => __('shop.validation.status_invalid'),
        ];
    }
}
