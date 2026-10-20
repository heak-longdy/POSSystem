<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class OrderBulkPrintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    protected function prepareForValidation(): void
    {
        $docType = $this->input('document_type') ?: $this->route('document_type');
        if ($docType) {
            $this->merge(['document_type' => $docType]);
        }

        if (!$this->print_locale) {
            $this->merge(['print_locale' => app()->getLocale() === 'km' ? 'km' : 'en']);
        }

        if (is_string($this->order_ids)) {
            $decoded = json_decode($this->order_ids, true);
            if (is_array($decoded)) {
                $this->merge(['order_ids' => $decoded]);
            } else {
                $parts = array_filter(array_map('trim', explode(',', $this->order_ids)));
                $this->merge(['order_ids' => array_values($parts)]);
            }
        }
    }

    public function rules(): array
    {
        $maxOrders = (int) config('order_printing.max_orders', 100);

        return [
            'document_type' => 'required|in:invoice,delivery_slip',
            'print_locale' => 'required|in:en,km',
            'order_ids' => 'required|array|min:1|max:' . $maxOrders,
            'order_ids.*' => 'required|integer|min:1|distinct',
            'status' => 'nullable|string',
            'payment_status' => 'nullable|string',
            'shop_id' => 'nullable|integer',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
            'search' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'document_type.required' => __('order_bulk_print.validation.document_type_required'),
            'document_type.in' => __('order_bulk_print.validation.document_type_invalid'),
            'print_locale.required' => __('order_bulk_print.validation.print_locale_required'),
            'print_locale.in' => __('order_bulk_print.validation.print_locale_invalid'),
            'order_ids.required' => __('order_bulk_print.validation.order_ids_required'),
            'order_ids.array' => __('order_bulk_print.validation.order_ids_invalid'),
            'order_ids.min' => __('order_bulk_print.validation.order_ids_min'),
            'order_ids.max' => __('order_bulk_print.validation.order_ids_max', ['max' => config('order_printing.max_orders', 100)]),
            'order_ids.*.integer' => __('order_bulk_print.validation.order_id_invalid'),
        ];
    }
}
