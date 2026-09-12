<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StaffRequest extends FormRequest
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
            'name'         => 'required|string|max:255',
            'position_id'  => 'nullable|exists:positions,id',
            'phone_number' => 'nullable|string|max:50',
            'email'        => 'nullable|email|max:255',
            'address'      => 'nullable|string',
            'status'       => 'required',
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required'      => __('staff.validation.name_required'),
            'name.max'           => __('staff.validation.name_max'),
            'position_id.exists' => __('staff.validation.position_exists'),
            'email.email'        => __('staff.validation.email_format'),
            'status.required'    => __('staff.validation.status_required'),
        ];
    }
}
