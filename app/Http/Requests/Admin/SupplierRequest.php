<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SupplierRequest extends FormRequest
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
            'name'  => 'required|max:50',
            'status' => 'required|max:1'
        ];
    }
    public function messages()
    {
        return [
            'name.required'    => __('supplier.validation.name_required'),
            'name.max'         => __('supplier.validation.name_max'),
            'status.required'  => __('supplier.validation.status_required'),
            'status.max'       => __('supplier.validation.status_max'),
            'status.numeric'   => __('supplier.validation.status_numeric')
        ];
    }
}
