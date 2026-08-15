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
            'name.required'      => 'Staff name is required',
            'name.max'           => 'Staff name must not exceed 255 characters',
            'position_id.exists' => 'Selected position is invalid',
            'email.email'        => 'Email address must be a valid email format',
            'status.required'    => 'Status is required',
        ];
    }
}
