<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PromotionRequest extends FormRequest
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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'title'  => 'required|max:250',
            'service_id' => 'required',
            'shop_id'  => 'required',
            'customer_id' => 'required',
            'product_id' => 'required',
            'description'  => 'required',
            'type'  => 'required|in:dollar,percentage',
            'discount' => 'required',
            'from_date' => 'required',
            'to_date' => 'required'
        ];
    }   
    public function messages()
    {
        return [
            'title.required' => "Title is required",
            'title.max' => "Title max character",
            'service_id.required' => "Service is required",
            'shop_id.required' => "Shop is required",
            'customer_id.required'   => "Customer is required",
            'product_id.required'   => "Product is required",
            'description.required' => "Description is required",
            'type.required'   => "Type is required",
            'type.in'   => "Type format invalid",
            'discount.required'   => "Discount is required",
            'from_date' => "From date is required",
            'to_date' => "To date is required",
        ];
    }
}
