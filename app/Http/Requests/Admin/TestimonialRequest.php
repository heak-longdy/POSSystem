<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TestimonialRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return \Illuminate\Support\Facades\Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name'            => 'required|max:255',
            'status'          => 'required|max:1',
            'description'     => 'required',
            'image'           => 'required',
        ];
    }
    public function messages()
    {
        return [
            'name.required' => 'The name is required.',
            'name.max' => 'The name may not be greater than 255 characters.',

            'status.required' => 'The status is required.',
            'status.max' => 'The status must be a single character.',

            'description.required' => 'The description is required.',
            
            'image.required' => 'Image is required.',
        ];
    }
}
