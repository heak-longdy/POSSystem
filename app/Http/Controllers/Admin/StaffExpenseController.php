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

    /**
     * Staff Expense Listing.
     */
    public function index(Request $req)
    {
        $data['status'] = $req->status;
        $data['routeName'] = $this->routeName;
        $search = $req->search ? $req->search : '';

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

        $onlyTrashed = ($req->status == 'trash');

        if (!$onlyTrashed) {
            $query = StaffExpense::with(['staff', 'shop', 'createdBy'])->where('status', $req->status);
        } else {
            $query = StaffExpense::with(['staff', 'shop', 'createdBy'])->onlyTrashed();
        }

        $query->dateBetween($filters['from_date'], $filters['to_date'])
              ->ofStaff($filters['staff_id'])
              ->ofType($filters['type'])
              ->ofShop($filters['shop_id']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('staff', function ($sq) use ($search) {
                    $sq->where('name', 'like', '%' . $search . '%')
                       ->orWhere('phone_number', 'like', '%' . $search . '%');
                })->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('type', 'like', '%' . $search . '%')
                  ->orWhere('amount', 'like', '%' . $search . '%');
            });
        }

        $data['data']      = $query->orderBy('expense_date', 'desc')->orderBy('id', 'desc')->paginate(50);
        $data['summary']   = $this->expenseService->calculateSummary($filters, $onlyTrashed);
        $data['staffList'] = Staff::where('status', 1)->orderBy('name')->get();
        $data['shops']     = Shop::where('status', 1)->orderBy('name')->get();
        $data['types']     = ExpenseType::cases();
        $data['filters']   = $filters;
        $data['exportUrl'] = route('admin-' . $this->routeName . '-export');

        return view($this->layout . 'index', $data);
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
        $expenses = StaffExpense::with(['staff', 'shop', 'createdBy'])->orderBy('expense_date', 'desc')->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['ID', 'Staff Name', 'Shop', 'Expense Type', 'Amount ($)', 'Expense Date', 'Description', 'Created By', 'Status', 'Created At'];

        $callback = function () use ($expenses, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($expenses as $expense) {
                $row = [
                    'ID'           => $expense->id,
                    'Staff Name'   => $expense->staff?->name ?? '---',
                    'Shop'         => $expense->shop?->name ?? '---',
                    'Expense Type' => is_object($expense->type) ? $expense->type->value : $expense->type,
                    'Amount ($)'   => number_format($expense->amount, 2),
                    'Expense Date' => $expense->expense_date?->format('Y-m-d') ?? $expense->expense_date,
                    'Description'  => $expense->description,
                    'Created By'   => $expense->createdBy?->name ?? '---',
                    'Status'       => $expense->status == 1 ? 'Active' : 'Disabled',
                    'Created At'   => $expense->created_at,
                ];

                fputcsv($file, array_values($row));
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
