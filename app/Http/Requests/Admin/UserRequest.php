<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Support\Language;

class UserRequest extends FormRequest
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
        $acceptedId = $this->id ?? '';
        return [
            "name"  => "required",
            "email" => "required|email|unique:users,email," . $acceptedId,
            // "phone" => "required|numeric|unique:users,phone," . $acceptedId,
            // "identity" => "required|numeric|unique:users,identity," . $acceptedId,
            "status"    => "required|numeric",
            "role"      => "nullable|string",
            "language_preference" => ["required", Rule::in(Language::activeCodes())],
            'password' => $acceptedId ? 'nullable':'required'.'|same:confirm_password|min:6',
            'confirm_password' => $acceptedId ? 'nullable':'required|min:6',
        ];
    }
    public function messages()
    {
        return [
            "name.required" => __('user.validation.name_required'),
            "status.required" => __('user.validation.status_required'),
            "status.numeric" => __('user.validation.status_numeric'),
            "language_preference.required" => __('user.validation.language_preference_required'),
            "language_preference.in" => __('user.validation.language_preference_in'),
            "email.required" => __('user.validation.email_required'),
            "email.unique" => __('user.validation.email_unique'),
            'email.email'    => __('user.validation.email_format'),
            "phone.unique" => __('user.validation.phone_unique'),
            "phone.required" => __('user.validation.phone_required'),
            "phone.numeric" => __('user.validation.phone_numeric'),
            "identity.unique" => __('user.validation.identity_unique'),
            "identity.required" => __('user.validation.identity_required'),
            "identity.numeric" => __('user.validation.identity_numeric'),
            "password" => __('user.validation.password_min'),
            'password.required' => __('user.validation.password_required'),
            'password.same' => __('user.validation.password_same'),
            'confirm_password.required' => __('user.validation.confirm_password_required'),
        ];
    }
}
