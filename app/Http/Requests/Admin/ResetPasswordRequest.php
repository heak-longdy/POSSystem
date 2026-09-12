<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ResetPasswordRequest extends FormRequest
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
            'password' => 'required|same:confirm_password|min:6',
            'confirm_password' => 'required|min:6',
        ];
    }
    public function messages()
    {
        return [
            'password.required' => __('user.validation.password_required'),
            'password.same' => __('user.validation.password_same'),
            'password.min' => __('user.validation.password_min'),
            'confirm_password.required' => __('user.validation.confirm_password_required'),
            'confirm_password.min' => __('user.validation.confirm_password_min'),
        ];
    }
}
