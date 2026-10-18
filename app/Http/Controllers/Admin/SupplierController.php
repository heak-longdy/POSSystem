<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Services\Tools;
use App\Http\Requests\Admin\SupplierRequest;

class SupplierController extends Controller
{
    protected $layout = 'admin::pages.supplier.';
    private $tools;
    private $table = Supplier::class;
    private $routeName = "supplier";
    public function __construct(Tools $serTool)
    {
        $this->tools = $serTool;
    }
    private function querySuppliers(Request $req)
    {
        $status = $req->status ?? 1;
        if ($status !== 'trash') {
            $query = Supplier::where('status', $status);
        } else {
            $query = Supplier::onlyTrashed();
        }

        if ($req->filled('search')) {
            $search = trim($req->search);
            $query->where('name', 'LIKE', '%' . $search . '%');
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

        $query = $this->querySuppliers($req);
        $data['data'] = $query->paginate(50);
        $data['exportUrl'] = route('admin-supplier-export', request()->query());
        return view($this->layout . 'index', $data);
    }

    public function report(Request $req)
    {
        $suppliers = $this->querySuppliers($req)->get();

        return response()->json([
            'status' => $req->status ?? 1,
            'rows'   => $suppliers,
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
        $data['id'] = $req->id;
        $data['data'] = Supplier::find($req->id);
        $data['routeName'] = $this->routeName;
        return view($this->layout . 'store', $data);
    }
    public function Save(SupplierRequest $req, $id = "")
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
        $fileName = 'suppliers_export_' . date('Y-m-d_H-i-s') . '.csv';
        $suppliers = $this->querySuppliers($req)->get();

        $headers = array(
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array(
            __('global.table.no'),
            __('supplier.table.name'),
            __('supplier.table.ordering'),
            __('supplier.table.status'),
            __('global.table.created_at'),
        );

        $callback = function() use($suppliers, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM
            fputcsv($file, $columns);

            foreach ($suppliers as $index => $supplier) {
                $statusLabel = $supplier->status == 1 ? __('supplier.form.status.active') : __('supplier.form.status.disable');

                fputcsv($file, array(
                    $index + 1,
                    $supplier->name ?? '',
                    $supplier->ordering ?? '',
                    $statusLabel,
                    $supplier->created_at ? $supplier->created_at->format('Y-m-d H:i:s') : '',
                ));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
