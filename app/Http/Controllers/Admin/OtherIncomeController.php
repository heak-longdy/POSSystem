<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OtherIncomePaymentRequest;
use App\Http\Requests\Admin\OtherIncomeRequest;
use App\Models\OtherIncome;
use App\Models\OtherIncomeCategory;
use App\Models\OtherIncomePayment;
use App\Models\Shop;
use App\Models\Supplier;
use App\Services\OtherIncomeService;
use App\Services\SupplierBonusCalculator;
use App\Services\Tools;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OtherIncomeController extends Controller
{
    protected $layout = 'admin::pages.otherIncome.';
    private $tools;
    private $table = OtherIncome::class;
    private $routeName = "other-income";

    protected $incomeService;
    protected $bonusCalculator;

    public function __construct(
        Tools $tools,
        OtherIncomeService $incomeService,
        SupplierBonusCalculator $bonusCalculator
    ) {
        $this->tools = $tools;
        $this->incomeService = $incomeService;
        $this->bonusCalculator = $bonusCalculator;
    }

    /**
     * Other Income Listing with Summary KPIs and Filters
     */
    public function index(Request $req)
    {
        $status = $req->status ?? 1;
        $data['status'] = $status;
        $data['routeName'] = $this->routeName;

        if (!$req->status) {
            return redirect()->route('admin-' . $this->routeName . '-list', 1);
        }

        $query = $this->incomeService->queryIncomes($req, $status);

        $data['data']       = $query->paginate(50)->appends($req->query());
        $data['categories'] = OtherIncomeCategory::where('status', 1)->orderBy('ordering')->get();
        $data['suppliers']  = Supplier::where('status', 1)->orderBy('name')->get();
        $data['shops']      = Shop::where('status', 1)->orderBy('name')->get();
        $data['filters']    = [
            'from_date'      => $req->get('from_date'),
            'to_date'        => $req->get('to_date'),
            'supplier_id'    => $req->get('supplier_id'),
            'category_id'    => $req->get('category_id'),
            'shop_id'        => $req->get('shop_id'),
            'payment_status' => $req->get('payment_status'),
            'quarter'        => $req->get('quarter'),
            'search'         => $req->get('search'),
        ];
        $data['exportUrl']  = route('admin-' . $this->routeName . '-export', request()->query());

        return view($this->layout . 'index', $data);
    }

    /**
     * JSON Stream for ExcelJS export
     */
    public function report(Request $req)
    {
        $status = $req->status ?? 1;
        $incomes = $this->incomeService->queryIncomes($req, $status)->get();

        return response()->json([
            'status' => 'success',
            'rows'   => $incomes,
        ]);
    }

    /**
     * Show Create Form
     */
    public function onCreate(Request $req)
    {
        $data['id']         = "";
        $data['data']       = null;
        $data['routeName']  = $this->routeName;
        $data['categories'] = OtherIncomeCategory::where('status', 1)->orderBy('ordering')->get();
        $data['suppliers']  = Supplier::where('status', 1)->orderBy('name')->get();
        $data['shops']      = Shop::where('status', 1)->orderBy('name')->get();
        $data['quarters']   = $this->getAvailableQuarters();
        $data['selectedSupplier'] = $req->filled('supplier_id') ? Supplier::find($req->supplier_id) : null;

        return view($this->layout . 'store', $data);
    }

    /**
     * Show Edit Form
     */
    public function onEdit(Request $req)
    {
        $income = OtherIncome::withTrashed()->with('payments')->find($req->id);
        if (!$income) {
            return redirect()->route('admin-' . $this->routeName . '-list', 1);
        }

        $data['id']         = $income->id;
        $data['data']       = $income;
        $data['routeName']  = $this->routeName;
        $data['categories'] = OtherIncomeCategory::where('status', 1)->orderBy('ordering')->get();
        $data['suppliers']  = Supplier::where('status', 1)->orderBy('name')->get();
        $data['shops']      = Shop::where('status', 1)->orderBy('name')->get();
        $data['quarters']   = $this->getAvailableQuarters();
        $data['selectedSupplier'] = $income->supplier;

        return view($this->layout . 'store', $data);
    }

    /**
     * Save / Store Other Income Record
     */
    public function Save(OtherIncomeRequest $req, $id = "")
    {
        try {
            $income = $this->incomeService->saveIncome($req, $id ?: null);
            $message = $id
                ? __('other_income.message.update_success')
                : __('other_income.message.create_success');

            if ($req->input('save_opt') === 'save_new') {
                return redirect()->route('admin-' . $this->routeName . '-create')->with('success', $message);
            }

            return redirect()->route('admin-' . $this->routeName . '-list', 1)->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Record an installment payment (AJAX or form)
     */
    public function storePayment(OtherIncomePaymentRequest $req, $id)
    {
        $income = OtherIncome::findOrFail($id);
        $receiptFile = $req->hasFile('receipt') ? $req->file('receipt') : null;

        $payment = $this->incomeService->recordPayment($income, $req->validated(), $receiptFile);

        if ($req->expectsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => __('other_income.message.payment_success'),
                'payment' => $payment,
                'income'  => $income->fresh(),
            ]);
        }

        return redirect()->back()->with('success', __('other_income.message.payment_success'));
    }

    /**
     * Delete an installment payment
     */
    public function deletePayment(Request $req, $paymentId)
    {
        $payment = OtherIncomePayment::findOrFail($paymentId);
        $this->incomeService->deletePayment($payment);

        if ($req->expectsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => __('other_income.message.payment_deleted'),
            ]);
        }

        return redirect()->back()->with('success', __('other_income.message.payment_deleted'));
    }

    /**
     * AJAX Supplier Purchases Aggregator & Bonus Calculator
     */
    public function calculatePurchases(Request $req)
    {
        $supplierId = (int) $req->get('supplier_id');
        if (!$supplierId) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Please select a supplier.',
            ], 422);
        }

        $period = $this->bonusCalculator->resolvePeriod(
            $req->get('quarter'),
            $req->get('from_date'),
            $req->get('to_date')
        );

        $shopId = $req->filled('shop_id') ? (int) $req->shop_id : null;
        $purchases = $this->bonusCalculator->aggregatePurchases($supplierId, $period['start'], $period['end'], $shopId);

        $calcModel    = $req->get('model', 'percentage');
        $rateOrTarget = (float) $req->get('rate_or_target', 0);
        $fixedBonus   = (float) $req->get('fixed_bonus', 0);

        $bonusResult = $this->bonusCalculator->computeBonus(
            $purchases['total_purchases'],
            $purchases['total_qty'],
            $calcModel,
            $rateOrTarget,
            $fixedBonus
        );

        return response()->json([
            'status'    => 'success',
            'period'    => $period,
            'purchases' => $purchases,
            'bonus'     => $bonusResult,
        ]);
    }

    /**
     * Detailed Payment History Ledger Modal Content
     */
    public function details(Request $req, $id)
    {
        $income = OtherIncome::with(['payments.createdBy', 'supplier', 'category', 'shop', 'createdBy'])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'income' => $income,
        ]);
    }

    /**
     * Per-Supplier Other Income History View
     */
    public function supplierHistory(Request $req, $supplierId)
    {
        $supplier = Supplier::findOrFail($supplierId);

        $req->merge(['supplier_id' => $supplierId]);
        $query = $this->incomeService->queryIncomes($req, 1);

        $data['supplier'] = $supplier;
        $data['incomes']  = $query->paginate(50)->appends($req->query());
        $data['summary']  = $this->incomeService->calculateSummary($query);
        $data['filters']  = $req->all();

        return view($this->layout . 'supplier-history', $data);
    }

    public function updateStatus($id, $status)
    {
        return $this->tools->onUpdateStatus($this->table, $id, $status);
    }

    public function restore($id = "")
    {
        return $this->tools->onRestore($this->table, $id);
    }

    public function delete($id = "")
    {
        return $this->tools->onDelete($this->table, $id);
    }

    public function destroy($id = "")
    {
        return $this->tools->onDestroy($this->table, $id);
    }

    private function getAvailableQuarters(): array
    {
        $currentYear = (int) date('Y');
        $quarters = [];
        for ($y = $currentYear; $y >= $currentYear - 2; $y--) {
            for ($q = 4; $q >= 1; $q--) {
                $quarters[] = [
                    'key'   => "{$y}-Q{$q}",
                    'label' => "{$y} Q{$q} (" . match($q) {
                        1 => 'Jan - Mar',
                        2 => 'Apr - Jun',
                        3 => 'Jul - Sep',
                        4 => 'Oct - Dec',
                    } . ")",
                ];
            }
        }
        return $quarters;
    }
}
