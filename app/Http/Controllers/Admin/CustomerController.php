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
    public function index(Request $req)
    {
        $data['status'] = $req->status;
        $data['routeName'] = $this->routeName;
        if (!$req->status) {
            return redirect()->route('admin-'.$this->routeName.'-list', 1);
        }
        if ($req->status != 'trash') {
            $query = Customer::where('status', $req->status);
        } else {
            $query = Customer::onlyTrashed();
        }
        $data['data'] = $query->orderBy('id', 'desc')->paginate(50);
        $data['exportUrl'] = route('admin-customer-export');
        return view($this->layout . 'index', $data);
    }
    public function onCreate()
    {
        $data['id'] = "";
        $data['routeName'] = $this->routeName;
        return view($this->layout . 'store', $data);
    }
    public function onEdit(Request $req)
    {
        $data['id'] = $req->id;
        $data['data'] = Customer::find($req->id);
        $data['routeName'] = $this->routeName;
        return view($this->layout . 'store', $data);
    }
    public function Save(CustomerRequest $req, $id = "")
    {
        if ($req->input('save_opt') == 'save_new') {
            return $this->tools->onSave($this->table, $req, $id, $this->routeName, 'back');
        }
        return $this->tools->onSave($this->table, $req, $id, $this->routeName);
    }
    public function updateStatus($id, $status)
    {
        return $this->tools->onUpdateStatus($this->table, $id, $status);
    }
    public function restore($id = "")
    {
        return $this->tools->onRestore($this->table, $id);
    }
    public function destroy($id = "")
    {
        return $this->tools->onDestroy($this->table, $id);
    }

    public function delete($id = "")
    {
        return $this->tools->onDelete($this->table, $id);
    }

    public function export(Request $req)
    {
        $fileName = 'customers_export_' . date('Y-m-d_H-i-s') . '.csv';
        $customers = Customer::all(); // You might want to apply filters here if needed

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('ID', 'Name', 'Phone', 'Email', 'Address', 'Created At');

        $callback = function() use($customers, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($customers as $customer) {
                $row['ID']  = $customer->id;
                $row['Name']    = $customer->name;
                $row['Phone']    = $customer->phone;
                $row['Email']  = $customer->email;
                $row['Address']  = $customer->address;
                $row['Created At']  = $customer->created_at;

                fputcsv($file, array($row['ID'], $row['Name'], $row['Phone'], $row['Email'], $row['Address'], $row['Created At']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
