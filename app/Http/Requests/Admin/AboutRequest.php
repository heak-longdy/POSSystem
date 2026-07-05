<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AboutRequest extends FormRequest
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
            'title' => 'required',
            'image' => 'required',
            'ser_des1' => 'required',
        ];
    }
    public function messages()
    {
        return [
            'title.required' => "Title is required",
            'image.required' => "Image is required",
            'ser_des1.required' => "Description is required",
            'ser_des1.max' => "Description cannot exceed 255 characters",
        ];
    }
}
