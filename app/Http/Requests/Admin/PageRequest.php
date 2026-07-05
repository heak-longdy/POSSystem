<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PageRequest extends FormRequest
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
            'title.km' => 'required|max:250',
            'type'  => 'required|in:contact,about,privacy,term-condition,member,garage',
            'status' => 'bail|required|max:2|numeric|in:1,2',
        ];
    }
    public function messages()
    {
        return [
            'title.km.required' => "Title khmer is required",
            'type.required' => "Type is required",
            'type.in'   => "Type format invalid",
        ];
    }
}
