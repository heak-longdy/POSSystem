<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ExpenseType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StaffExpenseRequest;
use App\Models\Shop;
use App\Models\Staff;
use App\Models\StaffExpense;
use App\Services\StaffExpenseService;
use App\Services\Tools;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StaffExpenseController extends Controller
{
    protected $layout = 'admin::pages.staffExpense.';
    private $tools;
    private $table = StaffExpense::class;
    private $routeName = "staff-expense";
    protected $expenseService;

    public function __construct(Tools $tools, StaffExpenseService $expenseService)
    {
        $this->tools = $tools;
        $this->expenseService = $expenseService;
    }

    private function queryStaffExpenses(Request $req)
    {
        $status = $req->status ?? 1;
        if ($status !== 'trash') {
            $query = StaffExpense::with(['staff', 'shop', 'createdBy'])->where('status', $status);
        } else {
            $query = StaffExpense::with(['staff', 'shop', 'createdBy'])->onlyTrashed();
        }

        $query->dateBetween($req->from_date, $req->to_date)
              ->ofStaff($req->staff_id ? (int) $req->staff_id : null)
              ->ofType($req->type)
              ->ofShop($req->shop_id ? (int) $req->shop_id : null);

        if ($req->filled('search')) {
            $search = trim($req->search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('staff', function ($sq) use ($search) {
                    $sq->where('name', 'LIKE', '%' . $search . '%')
                       ->orWhere('phone_number', 'LIKE', '%' . $search . '%');
                })->orWhere('description', 'LIKE', '%' . $search . '%')
                  ->orWhere('type', 'LIKE', '%' . $search . '%')
                  ->orWhere('amount', 'LIKE', '%' . $search . '%');
            });
        }

        return $query->orderBy('expense_date', 'desc')->orderBy('id', 'desc');
    }

    /**
     * Staff Expense Listing.
     */
    public function index(Request $req)
    {
        $data['status'] = $req->status;
        $data['routeName'] = $this->routeName;

        if (!$req->status) {
            return redirect()->route('admin-' . $this->routeName . '-list', 1);
        }

        $filters = [
            'from_date' => $req->get('from_date'),
            'to_date'   => $req->get('to_date'),
            'staff_id'  => $req->get('staff_id'),
            'type'      => $req->get('type'),
            'shop_id'   => $req->get('shop_id'),
        ];

        $query = $this->queryStaffExpenses($req);

        $data['data']      = $query->paginate(50);
        $data['staffList'] = Staff::where('status', 1)->orderBy('name')->get();
        $data['shops']     = Shop::where('status', 1)->orderBy('name')->get();
        $data['types']     = ExpenseType::cases();
        $data['filters']   = $filters;
        $data['exportUrl'] = route('admin-' . $this->routeName . '-export', request()->query());

        return view($this->layout . 'index', $data);
    }

    public function report(Request $req)
    {
        $expenses = $this->queryStaffExpenses($req)->get();

        return response()->json([
            'status' => $req->status ?? 1,
            'rows'   => $expenses,
        ]);
    }

    /**
     * Create Form.
     */
    public function onCreate()
    {
        $data['id']        = "";
        $data['routeName'] = $this->routeName;
        $data['staffList'] = Staff::where('status', 1)->orderBy('name')->get();
        $data['shops']     = Shop::where('status', 1)->orderBy('name')->get();
        $data['types']     = ExpenseType::cases();

        return view($this->layout . 'store', $data);
    }

    /**
     * Edit Form.
     */
    public function onEdit(Request $req)
    {
        $data['id']        = $req->id;
        $data['data']      = StaffExpense::withTrashed()->find($req->id);
        $data['routeName'] = $this->routeName;
        $data['staffList'] = Staff::where('status', 1)->orderBy('name')->get();
        $data['shops']     = Shop::where('status', 1)->orderBy('name')->get();
        $data['types']     = ExpenseType::cases();

        return view($this->layout . 'store', $data);
    }

    /**
     * Save Expense Record (Create or Update).
     */
    public function Save(StaffExpenseRequest $req, $id = "")
    {
        if ($req->input('save_opt') == 'save_new') {
            return $this->tools->onSave($this->table, $req, $id, $this->routeName, 'back');
        }
        return $this->tools->onSave($this->table, $req, $id, $this->routeName);
    }

    /**
     * Update Status (Active / Disable).
     */
    public function updateStatus($id, $status)
    {
        return $this->tools->onUpdateStatus($this->table, $id, $status);
    }

    /**
     * Restore Soft Deleted Expense.
     */
    public function restore($id = "")
    {
        return $this->tools->onRestore($this->table, $id);
    }

    /**
     * Soft Delete Expense.
     */
    public function delete($id = "")
    {
        return $this->tools->onDelete($this->table, $id);
    }

    /**
     * Permanent Force Delete Expense.
     */
    public function destroy($id = "")
    {
        return $this->tools->onDestroy($this->table, $id);
    }

    /**
     * Export Staff Expense Records to CSV.
     */
    public function export(Request $req)
    {
        $fileName = 'staff_expenses_export_' . date('Y-m-d_H-i-s') . '.csv';
        $expenses = $this->queryStaffExpenses($req)->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            __('global.table.no'),
            __('staff_expense.table.staff_name'),
            __('staff_expense.table.shop'),
            __('staff_expense.table.type'),
            __('staff_expense.table.amount'),
            __('staff_expense.table.expense_date'),
            __('staff_expense.table.remarks'),
            __('staff_expense.table.recorded_by'),
            __('global.table.created_at'),
        ];

        $callback = function () use ($expenses, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM
            fputcsv($file, $columns);

            foreach ($expenses as $index => $expense) {
                $typeLabel = $expense->type instanceof ExpenseType ? $expense->type->label() : ($expense->type ?? '');
                $expenseDate = $expense->expense_date instanceof Carbon ? $expense->expense_date->format('Y-m-d') : ($expense->expense_date ?? '');
                fputcsv($file, [
                    $index + 1,
                    $expense->staff?->name ?? '---',
                    $expense->shop?->name ?? __('staff_expense.all_shops'),
                    $typeLabel,
                    number_format((float) $expense->amount, 2),
                    $expenseDate,
                    $expense->description ?? '',
                    $expense->createdBy?->name ?? '---',
                    $expense->created_at ? $expense->created_at->format('Y-m-d H:i:s') : '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Per-Staff Expense Ledger View.
     */
    public function staffHistory(Request $req, $staffId)
    {
        $staff = Staff::findOrFail($staffId);

        $filters = [
            'from_date' => $req->get('from_date', Carbon::now()->startOfYear()->format('Y-m-d')),
            'to_date'   => $req->get('to_date', Carbon::now()->endOfDay()->format('Y-m-d')),
            'staff_id'  => $staffId,
            'type'      => $req->get('type'),
        ];

        $expenses = $this->expenseService->getFilteredExpenses($filters, 50);
        $summary  = $this->expenseService->calculateSummary($filters);
        $types    = ExpenseType::cases();

        return view($this->layout . 'staff-history', compact('staff', 'expenses', 'summary', 'types', 'filters'));
    }
}
