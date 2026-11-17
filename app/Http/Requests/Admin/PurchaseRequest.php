<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'supplier_id'         => ['required', 'exists:suppliers,id'],
            'shop_id'             => ['required', 'exists:shops,id'],
            'purchase_date'       => ['required', 'date'],
            'delivery_date'       => ['nullable', 'date'],
            'payment_due_date'    => ['nullable', 'date'],
            'supplier_invoice_no' => ['nullable', 'string', 'max:100'],
            'status'              => ['nullable', 'in:draft,ordered,received,partial_received,cancelled'],
            'discount_type'       => ['nullable', 'in:fixed,percentage'],
            'discount_rate'       => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_amount'     => ['nullable', 'numeric', 'min:0'],
            'tax_rate'            => ['nullable', 'numeric', 'min:0', 'max:100'],
            'shipping_cost'       => ['nullable', 'numeric', 'min:0'],
            'remark'              => ['nullable', 'string', 'max:1000'],
            'items'               => ['required', 'array', 'min:1'],
            'items.*.product_id'  => ['required', 'exists:products,id'],
            'items.*.ordered_qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_cost'   => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_amount'      => ['nullable', 'numeric', 'min:0'],
            'items.*.remark'          => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'supplier_id.required'         => __('purchase.validation.supplier_required', ['default' => 'Please select a supplier.']),
            'shop_id.required'             => __('purchase.validation.shop_required', ['default' => 'Please select a destination shop/warehouse.']),
            'purchase_date.required'       => __('purchase.validation.date_required', ['default' => 'Purchase date is required.']),
            'items.required'               => __('purchase.validation.items_required', ['default' => 'At least one product item is required.']),
            'items.min'                    => __('purchase.validation.items_min', ['default' => 'At least one product item is required.']),
            'items.*.product_id.required'  => __('purchase.validation.item_product_required', ['default' => 'Product is required for all line items.']),
            'items.*.ordered_qty.required' => __('purchase.validation.item_qty_required', ['default' => 'Quantity must be at least 0.01.']),
            'items.*.ordered_qty.min'      => __('purchase.validation.item_qty_min', ['default' => 'Quantity must be greater than 0.']),
            'items.*.unit_cost.required'   => __('purchase.validation.item_cost_required', ['default' => 'Unit cost is required.']),
        ];
    }
}
