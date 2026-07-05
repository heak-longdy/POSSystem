<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BlogRequest extends FormRequest
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
            'title'           => 'required|max:255',
            'status'          => 'required|max:1',
            'des'     => 'required',
            'image'           => 'required',
        ];
    }
    public function messages()
    {
        return [
            'title.required' => 'The title is required.',
            'title.max' => 'The job title may not be greater than 255 characters.',

            'status.required' => 'The job status is required.',
            'status.max' => 'The job status must be a single character.',

            'des.required' => 'The description is required.',
            
            'image.required' => 'Image is required.',
        ];
    }
}
