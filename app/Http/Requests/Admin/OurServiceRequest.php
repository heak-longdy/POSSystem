<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class OurServiceRequest extends FormRequest
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
            // 'image' => 'required',
            'ser_title1' => 'required|max:75',
            'ser_des1' => 'required|max:350',
            'ser_title2' => 'max:75',
            'ser_des2' => 'max:350',
            'ser_title3' => 'max:75',
            'ser_des3' => 'max:350'
        ];
    }
    public function messages()
    {
        return [
            'title.required' => "Title is required",
            'image.required' => "Image is required",
            // 'image.image' => "The uploaded file must be an image.",
            // 'image.mimes' => "Image must be a file of type: jpeg, png, jpg, gif.",
            // 'image.max' => "Image size must not exceed 2MB.",

            'ser_title1.required' => "Title is required",
            'ser_title1.max' => "Title cannot exceed 75 characters",

            'ser_des1.required' => "Description is required",
            'ser_des1.max' => "Description cannot exceed 255 characters",

            'ser_title2.max' => "Title cannot exceed 75 characters",
            'ser_des2.max' => "Description cannot exceed 255 characters",

            'ser_title3.max' => "Title cannot exceed 75 characters",
            'ser_des3.max' => "Description cannot exceed 255 characters",
        ];
    }
}
