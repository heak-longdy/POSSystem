<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class InvoiceSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'prefix'                => 'required|string|max:20',
            'separator'             => 'nullable|string|max:5',
            'date_format'           => 'required|in:none,Y,Ym,Y-m,Ymd',
            'digit_length'          => 'required|integer|min:2|max:10',
            'start_number'          => 'required|integer|min:1|max:999999',
            'reset_cycle'           => 'required|in:never,yearly,monthly,daily',
            'reset_active_counter'  => 'nullable|boolean',
        ];
    }
}
