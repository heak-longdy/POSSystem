<?php

namespace App\Http\Requests\Admin;

use App\Support\PriceNormalizer;
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
     * Prepare the data for validation.
     * Normalizes price and cost (handling both period and comma decimal separators,
     * thousands separators, and Khmer keyboard inputs).
     */
    protected function prepareForValidation()
    {
        $merge = [];
        if ($this->has('price') && $this->input('price') !== null && $this->input('price') !== '') {
            $parsedPrice = PriceNormalizer::parse($this->input('price'));
            if ($parsedPrice['valid']) {
                $merge['price'] = $parsedPrice['numeric_string'];
            }
        }
        if ($this->has('cost') && $this->input('cost') !== null && $this->input('cost') !== '') {
            $parsedCost = PriceNormalizer::parse($this->input('cost'));
            if ($parsedCost['valid']) {
                $merge['cost'] = $parsedCost['numeric_string'];
            }
        }
        if (!empty($merge)) {
            $this->merge($merge);
        }
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
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('products', 'code')->ignore($this->route('id'))->whereNull('deleted_at'),
            ],
            'name' => [
                'required',
                'max:50',
                Rule::unique('products', 'name')->ignore($this->route('id')),
            ],
            'cost' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'status' => 'required|max:1',
        ];
    }

    public function messages()
    {
        return [
            'category_id.required' => __('product.validation.category_required'),
            'uom_id.required' => __('product.validation.uom_required'),
            'code.max' => __('product.validation.code_max'),
            'code.unique' => __('product.validation.code_unique'),
            'name.required' => __('product.validation.name_required'),
            'name.max' => __('product.validation.name_max'),
            'name.unique' => __('product.validation.name_unique'),
            'cost.required' => __('product.validation.cost_required'),
            'price.required' => __('product.validation.price_required'),
            'cost.numeric' => __('product.validation.cost_numeric'),
            'price.numeric' => __('product.validation.price_numeric'),
            'cost.min' => __('product.validation.cost_min'),
            'price.min' => __('product.validation.price_min'),
            'status.required' => __('product.validation.status_required'),
            'status.max' => __('product.validation.status_max'),
        ];
    }
}
