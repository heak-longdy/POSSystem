<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomerPaidRequest;
use App\Models\CustomerPaid;
use Illuminate\Http\Request;
use App\Services\Tools;

class CustomerPaidController extends Controller
{
    protected $layout = 'admin::pages.customerPaid.';
    private $tools;
    private $table = CustomerPaid::class;
    private $routeName = "customer-paid";
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
            $query = $this->table::where('status', $req->status);
        } else {
            $query = $this->table::onlyTrashed();
        }
        if ($req->search) {
            $search = $req->search;
            $query = $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', '%' . $search . '%');
                $q->orWhere('des', 'like', '%' . $search . '%');
                $q->orWhere('customer_id', 'like', '%' . $search . '%');
                $q->orWhere('amount_usd', 'like', '%' . $search . '%');
                $q->orWhere('amount_kh', 'like', '%' . $search . '%');
            });
        }
        $data['data'] = $query->orderBy('created_at', 'desc')->paginate(50);
        $data['exportUrl'] = route('admin-customer-paid-export');
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
        $data['data'] = $this->table::find($req->id);
        $data['routeName'] = $this->routeName;
        return view($this->layout . 'store', $data);
    }
    public function Save(CustomerPaidRequest $req, $id = "")
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
        $fileName = 'customer_paid_export_' . date('Y-m-d_H-i-s') . '.csv';
        $payments = CustomerPaid::all();

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('ID', 'Customer Name', 'Amount USD', 'Amount KHR', 'Description', 'Created At');

        $callback = function() use($payments, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($payments as $payment) {
                $row['ID']  = $payment->id;
                $row['Customer Name']    = $payment->customer_name;
                $row['Amount USD']    = $payment->amount_usd;
                $row['Amount KHR']  = $payment->amount_kh;
                $row['Description']  = $payment->des;
                $row['Created At']  = $payment->created_at;

                fputcsv($file, array($row['ID'], $row['Customer Name'], $row['Amount USD'], $row['Amount KHR'], $row['Description'], $row['Created At']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
