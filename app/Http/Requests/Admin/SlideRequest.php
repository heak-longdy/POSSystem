<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SlideRequest extends FormRequest
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
            'name.km' =>  'required|string',
            'ordering' => 'required|numeric',
            'slug' => 'nullable|max:150|string|unique:slides,slug,' . $acceptedId,
            'image' => 'required'
        ];
    }
    public function messages()
    {
        return [
            'name.km.required' => "Name khmer is required",
            'ordering.required' => "Ordering is required",
            'ordering.numeric'   => "Ordering format invalid",
            'image.required' => "Image is required",
        ];
    }
}
