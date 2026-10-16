<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user() && $this->user()->can('user-update');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'permission' => ['sometimes', 'array'],
            'permission.*' => ['required', 'string', 'distinct',
                Rule::exists('permissions', 'name')->where('guard_name', 'web')],
        ];
    }

    public function messages()
    {
        return [
            'permission.array' => __('permission.validation.array'),
            'permission.*.required' => __('permission.validation.required'),
            'permission.*.string' => __('permission.validation.string'),
            'permission.*.distinct' => __('permission.validation.distinct'),
            'permission.*.exists' => __('permission.validation.exists'),
        ];
    }
}
