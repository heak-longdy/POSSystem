<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
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
            'email' => 'required|email',
            'name' => 'required',
            'subject' => 'required',
            'description' => 'required',
        ];
    }
    public function messages()
    {
        return [
            'name.required' => 'Name is required.',
            'subject.required' => 'Subject is required.',
            'email.email'    => 'Please provide a valid email address.',
            'email.required' => 'Email is required.',
            'description.required' => 'Description is required.',
        ];
    }
}
