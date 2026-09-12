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
                $validator->errors()->add('to_shop_id', __('stock_transfer.validation.to_shop_different'));
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
                $validator->errors()->add('qty', __('stock_transfer.validation.qty_limited'));
            }
        });
    }

    public function messages()
    {
        return [
            'shop_id.required'    => __('stock_transfer.validation.from_shop_required'),
            'shop_id.exists'      => __('stock_transfer.validation.from_shop_invalid'),
            'to_shop_id.required' => __('stock_transfer.validation.to_shop_required'),
            'to_shop_id.exists'   => __('stock_transfer.validation.to_shop_invalid'),
            'product_id.required' => __('stock_transfer.validation.product_required'),
            'product_id.exists'   => __('stock_transfer.validation.product_invalid'),
            'qty.required'        => __('stock_transfer.validation.qty_required'),
            'qty.integer'         => __('stock_transfer.validation.qty_integer'),
            'qty.min'             => __('stock_transfer.validation.qty_min'),
            'remark.max'          => __('stock_transfer.validation.remark_max'),
        ];
    }
}
