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
    private function queryUoms(Request $req)
    {
        $status = $req->status ?? 1;
        if ($status !== 'trash') {
            $query = UOM::where('status', $status);
        } else {
            $query = UOM::onlyTrashed();
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

        $query = $this->queryUoms($req);
        $data['data'] = $query->paginate(50);
        $data['exportUrl'] = route('admin-uom-export', request()->query());
        return view($this->layout . 'index', $data);
    }

    public function report(Request $req)
    {
        $uoms = $this->queryUoms($req)->get();

        return response()->json([
            'status' => $req->status ?? 1,
            'rows'   => $uoms,
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
        $uoms = $this->queryUoms($req)->get();

        $headers = array(
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array(
            __('global.table.no'),
            __('uom.table.name'),
            __('uom.table.status'),
            __('global.table.created_at'),
        );

        $callback = function() use($uoms, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM
            fputcsv($file, $columns);

            foreach ($uoms as $index => $uom) {
                $statusLabel = $uom->status == 1 ? __('uom.form.status.active') : __('uom.form.status.disable');

                fputcsv($file, array(
                    $index + 1,
                    $uom->name ?? '',
                    $statusLabel,
                    $uom->created_at ? $uom->created_at->format('Y-m-d H:i:s') : '',
                ));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
