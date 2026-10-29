<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
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
     */
    protected function prepareForValidation()
    {
        if ($this->has('code')) {
            $code = trim((string) $this->input('code'));
            $this->merge([
                'code' => $code === '' ? null : $code,
            ]);
        }
        if ($this->has('name')) {
            $this->merge([
                'name' => trim((string) $this->input('name')),
            ]);
        }
        if ($this->has('phone')) {
            $this->merge([
                'phone' => trim((string) $this->input('phone')),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $id = $this->route('id') ?? $this->input('id');

        return [
            'code'       => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('customers', 'code')->ignore($id)->whereNull('deleted_at'),
            ],
            'name'       => 'required|string|max:255',
            'phone'      => 'required|string|max:50',
            'address'    => 'nullable|string|max:500',
            'profile'    => 'nullable|string|max:255',
            'status'     => 'required|in:1,2',
            'payment_restriction' => 'nullable|in:N,Y',
        ];
    }
    public function messages()
    {
        return [
            'code.max'             => __('customer.validation.code_max'),
            'code.unique'          => __('customer.validation.code_unique'),
            'name.required'        => __('customer.validation.name_required'),
            'name.max'             => __('customer.validation.name_max'),
            'phone.required'       => __('customer.validation.phone_required'),
            'phone.max'            => __('customer.validation.phone_max'),
            'status.required'      => __('customer.validation.status_required'),
            'status.in'            => __('customer.validation.status_invalid'),
            'payment_restriction.in' => __('customer.validation.payment_restriction_invalid'),
        ];
    }
}
