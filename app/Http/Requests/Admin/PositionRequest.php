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
            'title.required' => __('position.validation.title_required'),
            'title.max' => __('position.validation.title_max'),
            'status.required' => __('position.validation.status_required'),
            'status.max' => __('position.validation.status_max'),
            'status.numeric' => __('position.validation.status_numeric'),
            'image.required' => __('position.validation.image_required'),
        ];
    }
}
