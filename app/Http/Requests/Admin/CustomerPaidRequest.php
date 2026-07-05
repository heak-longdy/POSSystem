<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CustomerPaidRequest extends FormRequest
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

    public function prepareForValidation()
    {
        if ($this->has('amount_usd')) {
            $val = str_replace(',', '', $this->amount_usd);
            $this->merge([
                'amount_usd' => $val === '' ? null : $val,
            ]);
        }
        if ($this->has('amount_kh')) {
            $val = str_replace(',', '', $this->amount_kh);
            $this->merge([
                'amount_kh' => $val === '' ? null : $val,
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
        $acceptedId = $this->id ?? '';
        return [
            "customer_id" => "required_without:des|nullable|unique:customer_paid,customer_id," . $acceptedId,
            'amount_usd'  => 'required_without:amount_kh|nullable|numeric|min:0.01',
            'amount_kh'  => 'required_without:amount_usd|nullable|numeric|min:0.01',
            'des'         => 'required_without:customer_id|nullable|max:1000',
        ];
    }
    public function messages()
    {
        return [
            'customer_id.required_without'         => 'Customer is required.',
            'customer_id.unique'           => 'This customer already has a payment record.',
            'amount_usd.required_without'  => 'Provide at least one amount: USD or KHR.',
            'amount_kh.required_without'   => 'Provide at least one amount: USD or KHR.',
            'amount_usd.numeric'           => 'Amount USD must be a number.',
            'amount_kh.numeric'            => 'Amount KHR must be a number.',
            'amount_usd.min'               => 'Amount USD must be at least 0.01.',
            'amount_kh.min'                => 'Amount KHR must be at least 0.01.',
            'des.required_without'         => 'Description is required.',
        ];
    }

}
