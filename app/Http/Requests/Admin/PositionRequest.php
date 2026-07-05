<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PositionRequest extends FormRequest
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
            'title'  => 'required|max:50',
            'status' => 'required|max:1'
        ];
    }
    public function messages()
    {
        return [
            'title.required' => "Title is required",
            'title.max' => "Title must not exceed 50 characters.",
            'status.required' => "Status is required",
            'status.max' => "Status must not exceed 1 characters.",
            'status.numeric'   => "Status format invalid",
            'image.required' => "Image is required",
        ];
    }
}
