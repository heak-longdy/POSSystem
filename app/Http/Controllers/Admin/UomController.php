<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UOM;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\UOMRequest;
use App\Services\Tools;

class UomController extends Controller
{
    protected $layout = 'admin::pages.uom.';
    private $tools;
    private $table = UOM::class;
    private $routeName = "uom";
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
            $query = UOM::where('status', $req->status);
        } else {
            $query = UOM::onlyTrashed();
        }
        $data['data'] = $query->orderBy('id', 'desc')->paginate(50);
        $data['exportUrl'] = route('admin-uom-export');
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
        $data['data'] = UOM::find($req->id);
        $data['routeName'] = $this->routeName;
        return view($this->layout . 'store', $data);
    }
    public function Save(UOMRequest $req, $id = "")
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
        $fileName = 'uoms_export_' . date('Y-m-d_H-i-s') . '.csv';
        $uoms = UOM::all(); // You might want to apply filters here if needed

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('ID', 'Name', 'Phone', 'Email', 'Address', 'Created At');

        $callback = function() use($uoms, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($uoms as $uom) {
                $row['ID']  = $uom->id;
                $row['Name']    = $uom->name;
                $row['Phone']    = $uom->phone;
                $row['Email']  = $uom->email;
                $row['Address']  = $uom->address;
                $row['Created At']  = $uom->created_at;

                fputcsv($file, array($row['ID'], $row['Name'], $row['Phone'], $row['Email'], $row['Address'], $row['Created At']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
