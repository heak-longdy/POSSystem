<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomerRequest;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Services\Tools;

class CustomerController extends Controller
{
    protected $layout = 'admin::pages.customer.';
    private $tools;
    private $table = Customer::class;
    private $routeName = "customer";
    public function __construct(Tools $serTool)
    {
        $this->tools = $serTool;
    }
    private function queryCustomers(Request $req)
    {
        $status = $req->status ?? 1;
        if ($status !== 'trash') {
            $query = Customer::forUser()->where('status', $status);
        } else {
            $query = Customer::forUser()->onlyTrashed();
        }

        if ($req->filled('search')) {
            $search = trim($req->search);
            $numericId = (ctype_digit($search) || is_numeric($search)) ? (int) ltrim($search, '0') : null;
            $query->where(function ($q) use ($search, $numericId) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                  ->orWhere('phone', 'LIKE', '%' . $search . '%')
                  ->orWhere('address', 'LIKE', '%' . $search . '%')
                  ->orWhere('code', 'LIKE', '%' . $search . '%');
                if ($numericId && $numericId > 0) {
                    $q->orWhere('id', $numericId);
                }
            });
        }

        return $query->orderBy('id', 'desc');
    }

    public function index(Request $req)
    {
        $data['status'] = $req->status;
        $data['routeName'] = $this->routeName;
        if (!$req->status) {
            return redirect()->route('admin-'.$this->routeName.'-list', 1);
        }
        $query = $this->queryCustomers($req);
        $data['data'] = $query->paginate(50);
        $data['exportUrl'] = route('admin-customer-export', request()->query());
        return view($this->layout . 'index', $data);
    }

    public function report(Request $req)
    {
        $customers = $this->queryCustomers($req)->get()->makeVisible(['created_at']);

        return response()->json([
            'status' => $req->status ?? 1,
            'rows'   => $customers,
        ]);
    }
    public function onCreate()
    {
        $data['id'] = "";
        $data['routeName'] = $this->routeName;
        return view($this->layout . 'store', $data);
    }
    public function onEdit(Request $req)
    {
        $customer = Customer::findOrFail($req->id);
        if (!$customer->isOwnedBy(auth()->user())) {
            abort(403, 'Unauthorized access to customer.');
        }

        $data['id'] = $req->id;
        $data['data'] = $customer;
        $data['routeName'] = $this->routeName;
        return view($this->layout . 'store', $data);
    }
    public function Save(CustomerRequest $req, $id = "")
    {
        $originalUser = null;
        if ($id) {
            $customer = Customer::findOrFail($id);
            if (!$customer->isOwnedBy(auth()->user())) {
                abort(403, 'Unauthorized access to customer.');
            }
            $originalUser = $customer->user;
        }

        if ($req->input('save_opt') == 'save_new') {
            $response = $this->tools->onSave($this->table, $req, $id, $this->routeName, 'back');
        } else {
            $response = $this->tools->onSave($this->table, $req, $id, $this->routeName);
        }

        if ($id && $originalUser !== null) {
            Customer::where('id', $id)->update(['user' => $originalUser]);
        }

        return $response;
    }
    public function updateStatus($id, $status)
    {
        $customer = Customer::findOrFail($id);
        if (!$customer->isOwnedBy(auth()->user())) {
            abort(403, 'Unauthorized access to customer.');
        }
        $originalUser = $customer->user;
        $response = $this->tools->onUpdateStatus($this->table, $id, $status);
        if ($originalUser !== null) {
            Customer::where('id', $id)->update(['user' => $originalUser]);
        }
        return $response;
    }
    public function restore($id = "")
    {
        if ($id) {
            $customer = Customer::withTrashed()->findOrFail($id);
            if (!$customer->isOwnedBy(auth()->user())) {
                abort(403, 'Unauthorized access to customer.');
            }
        }
        return $this->tools->onRestore($this->table, $id);
    }
    public function destroy($id = "")
    {
        if ($id) {
            $customer = Customer::withTrashed()->findOrFail($id);
            if (!$customer->isOwnedBy(auth()->user())) {
                abort(403, 'Unauthorized access to customer.');
            }
        }
        return $this->tools->onDestroy($this->table, $id);
    }

    public function delete($id = "")
    {
        if ($id) {
            $customer = Customer::findOrFail($id);
            if (!$customer->isOwnedBy(auth()->user())) {
                abort(403, 'Unauthorized access to customer.');
            }
        }
        return $this->tools->onDelete($this->table, $id);
    }

    public function export(Request $req)
    {
        $fileName = 'customers_export_' . date('Y-m-d_H-i-s') . '.csv';
        $customers = $this->queryCustomers($req)->get();

        $headers = array(
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array(
            __('global.table.no'),
            __('customer.table.code'),
            __('global.table.name'),
            __('customer.form.phone.label'),
            __('customer.form.address.label'),
            __('global.table.created_at'),
        );

        $callback = function() use($customers, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM
            fputcsv($file, $columns);

            foreach ($customers as $index => $customer) {
                fputcsv($file, array(
                    $index + 1,
                    $customer->code ?? '',
                    $customer->name ?? '',
                    $customer->phone ?? '',
                    $customer->address ?? '',
                    $customer->created_at ? $customer->created_at->format('Y-m-d H:i:s') : '',
                ));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
