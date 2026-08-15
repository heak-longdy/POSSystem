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
            'products.*.commission_type' => 'nullable|in:khr,percent',
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
                    $validator->errors()->add("products.{$index}.commission_type", 'Commission type is required.');
                }

                if ($shopProductId) {
                    $shopProduct = ShopProduct::where('id', $shopProductId)
                        ->where('shop_id', $shopId)
                        ->first();

                    if (!$shopProduct) {
                        $validator->errors()->add("products.{$index}.product_id", 'This shop product does not belong to the selected shop.');
                    } elseif ((int) $shopProduct->product_id !== (int) $productId) {
                        $validator->errors()->add("products.{$index}.product_id", 'Existing shop product cannot be changed to another product.');
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
                    $validator->errors()->add("products.{$index}.product_id", 'This product already exists in this shop.');
                }
            }
        });
    }

    public function messages()
    {
        return [
            'products.required' => 'Please add at least one product.',
            'products.array' => 'Product data format is invalid.',
            'products.min' => 'Please add at least one product.',
            'products.*.product_id.required' => 'Product is required.',
            'products.*.product_id.exists' => 'Selected product does not exist.',
            'products.*.product_id.distinct' => 'Product must not be duplicated.',
            'products.*.price.required' => 'Price is required.',
            'products.*.price.numeric' => 'Price format invalid.',
            'products.*.price.min' => 'Price must be greater than or equal to 0.',
            'products.*.point.numeric' => 'Point format invalid.',
            'products.*.point.min' => 'Point must be greater than or equal to 0.',
            'products.*.max_qty.integer' => 'Max quantity format invalid.',
            'products.*.max_qty.min' => 'Max quantity must be greater than or equal to 0.',
            'products.*.commission.numeric' => 'Commission format invalid.',
            'products.*.commission.min' => 'Commission must be greater than or equal to 0.',
            'products.*.commission_type.required_with' => 'Commission type is required.',
            'products.*.commission_type.in' => 'Commission type is invalid.',
            'products.*.status.required' => 'Status is required.',
            'products.*.status.in' => 'Status is invalid.',
        ];
    }
}
