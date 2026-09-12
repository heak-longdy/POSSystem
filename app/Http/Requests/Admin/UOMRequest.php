<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UOMRequest extends FormRequest
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
            'name.required'    => __('uom.validation.name_required'),
            'name.max'         => __('uom.validation.name_max'),
            'status.required'  => __('uom.validation.status_required'),
            'status.max'       => __('uom.validation.status_max'),
            'status.numeric'   => __('uom.validation.status_numeric')
        ];
    }
}
