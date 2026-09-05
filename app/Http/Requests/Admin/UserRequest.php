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
            "name.required" => "Name is required",
            "status.required" => "Status is required",
            "status.numeric" => "Status is invalid format",
            "language_preference.required" => "Language Preference is required",
            "language_preference.in" => "Language Preference is invalid",
            "email.required" => "Email is required",
            "email.unique" => "Email already exists",
            'email.email'    => 'Please provide a valid email address.',
            "phone.unique" => "Phone number already exists",
            "phone.required" => "Phone is required",
            "phone.numeric" => "Phone is invalid format",
            "identity.unique" => "Identity already exists",
            "identity.required" => "Identity is required",
            "identity.numeric" => "Identity is invalid format",
            "password" => "Your password is too short, Must be 6 or more",
            'password.required' => "Password is required",
            'password.same' => "The password not match confirm password",
            'confirm_password.required' => "Confirm Password is required",
        ];
    }
}
