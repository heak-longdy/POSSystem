<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InvoiceSettingRequest;
use App\Models\InvoiceSequence;
use App\Models\InvoiceSetting;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class InvoiceSettingController extends Controller
{
    protected $layout = 'admin::pages.setting.';
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Display the invoice setting form.
     */
    public function index()
    {
        $setting = InvoiceSetting::getActive();
        $preview = $this->invoiceService->preview();

        return view($this->layout . 'invoice', [
            'setting' => $setting,
            'preview' => $preview,
        ]);
    }

    /**
     * Save the invoice settings.
     */
    public function save(InvoiceSettingRequest $request)
    {
        $setting = InvoiceSetting::getActive();

        $data = $request->validated();
        $setting->update([
            'prefix'       => strtoupper(trim($data['prefix'])),
            'separator'    => $data['separator'] ?? '',
            'date_format'  => $data['date_format'],
            'digit_length' => (int) $data['digit_length'],
            'start_number' => (int) $data['start_number'],
            'reset_cycle'  => $data['reset_cycle'],
        ]);

        // If user explicitly requested to reset active counter
        if ($request->boolean('reset_active_counter')) {
            $prefixKey = $this->invoiceService->buildPrefixKey($setting, now());
            $initialNumber = max(0, (int) $data['start_number'] - 1);

            InvoiceSequence::updateOrCreate(
                ['prefix_key' => $prefixKey],
                ['last_number' => $initialNumber]
            );
        }

        Session::flash('success', __('setting.invoice.save_success'));

        return redirect()->back();
    }

    /**
     * Return live preview via AJAX if requested.
     */
    public function preview(Request $request)
    {
        $preview = $this->invoiceService->preview($request->all());

        return response()->json([
            'status'  => 'success',
            'preview' => $preview,
        ]);
    }
}
