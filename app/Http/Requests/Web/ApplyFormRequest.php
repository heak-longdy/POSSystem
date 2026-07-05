<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class ApplyFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'job_title' => 'required',
            'email' => 'required|email',
            'files' => 'required|max:2048',
            'name' => 'required',
            'phone' => ['bail', 'required', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:9','max:15'],
        ];
    }
    public function messages()
    {
        return [
            'email.required' => 'Email is required.',
            'email.email'    => 'Please provide a valid email address.',
            'files.required' => 'File is required.',
            'files.max' => 'Each file may not be greater than 2MB.',
            'name.required'  => 'Name is required.',
            'phone.required' => 'Phone number is required.',
            'phone.regex'    => 'Phone number format is invalid.',
            'phone.min'      => 'Phone number must be at least 9 digits.',
            'phone.max'      => 'Phone number may not be greater than 15 digits.',
        ];
    }
}
