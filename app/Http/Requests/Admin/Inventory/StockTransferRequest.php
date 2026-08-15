<?php

namespace App\Http\Requests\Admin\Inventory;

use App\Models\StockOnHand;
use App\Models\StockTransfer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StockTransferRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    public function rules()
    {
        return [
            'shop_id' => [
                'required',
                Rule::exists('shops', 'id')->where('status', 1),
            ],
            'to_shop_id' => [
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

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if ((int) $this->input('shop_id') === (int) $this->input('to_shop_id')) {
                $validator->errors()->add('to_shop_id', 'To shop must be different from from shop');
                return;
            }

            $stockOnHand = StockOnHand::where('shop_id', $this->input('shop_id'))
                ->where('product_id', $this->input('product_id'))
                ->first();
            $availableStock = $stockOnHand ? (int) $stockOnHand->current_stock : 0;
            $stockTransfer = $this->route('id') ? StockTransfer::find($this->route('id')) : null;

            if ($stockTransfer
                && (int) $stockTransfer->status === 1
                && (int) $stockTransfer->from_shop_id === (int) $this->input('shop_id')
                && (int) $stockTransfer->product_id === (int) $this->input('product_id')) {
                $availableStock += (int) $stockTransfer->qty;
            }

            if ($availableStock < (int) $this->input('qty')) {
                $validator->errors()->add('qty', 'Qty is limited or out of stock');
            }
        });
    }

    public function messages()
    {
        return [
            'shop_id.required' => 'From shop is required',
            'shop_id.exists' => 'From shop is invalid',
            'to_shop_id.required' => 'To shop is required',
            'to_shop_id.exists' => 'To shop is invalid',
            'product_id.required' => 'Product is required',
            'product_id.exists' => 'Product is invalid',
            'qty.required' => 'Qty is required',
            'qty.integer' => 'Qty format invalid',
            'qty.min' => 'Qty must be at least 1',
            'remark.max' => 'Remark must not exceed 1000 characters.',
        ];
    }
}
