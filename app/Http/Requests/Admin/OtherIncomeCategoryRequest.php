<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class OtherIncomeCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        $id = $this->route('id') ?? '';
        return [
            'name'              => 'required|string|max:100',
            'code'              => 'required|string|max:50|unique:other_income_categories,code,' . $id,
            'requires_supplier' => 'nullable|boolean',
            'description'       => 'nullable|string|max:255',
            'ordering'          => 'nullable|integer',
            'status'            => 'required|integer',
        ];
    }
}
