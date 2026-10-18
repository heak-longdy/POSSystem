<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StaffRequest;
use App\Models\Position;
use App\Models\Staff;
use Illuminate\Http\Request;
use App\Services\Tools;

class StaffController extends Controller
{
    protected $layout = 'admin::pages.staff.';
    private $tools;
    private $table = Staff::class;
    private $routeName = "staff";

    public function __construct(Tools $tools)
    {
        $this->tools = $tools;
    }

    private function queryStaffs(Request $req)
    {
        $status = $req->status ?? 1;
        if ($status !== 'trash') {
            $query = Staff::with('position')->where('status', $status);
        } else {
            $query = Staff::with('position')->onlyTrashed();
        }

        if ($req->filled('position_id')) {
            $query->where('position_id', $req->position_id);
        }

        if ($req->filled('search')) {
            $search = trim($req->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                  ->orWhere('phone_number', 'LIKE', '%' . $search . '%')
                  ->orWhere('email', 'LIKE', '%' . $search . '%')
                  ->orWhere('address', 'LIKE', '%' . $search . '%');
            });
        }

        return $query->orderBy('id', 'desc');
    }

    public function index(Request $req)
    {
        $data['status'] = $req->status;
        $data['routeName'] = $this->routeName;

        if (!$req->status) {
            return redirect()->route('admin-' . $this->routeName . '-list', 1);
        }

        $query = $this->queryStaffs($req);
        $data['data'] = $query->paginate(50);
        $data['positions'] = Position::where('status', 1)->get();
        $data['exportUrl'] = route('admin-staff-export', request()->query());

        return view($this->layout . 'index', $data);
    }

    public function report(Request $req)
    {
        $staffs = $this->queryStaffs($req)->get();

        return response()->json([
            'status' => $req->status ?? 1,
            'rows'   => $staffs,
        ]);
    }

    public function onCreate()
    {
        $data['id'] = "";
        $data['routeName'] = $this->routeName;
        $data['positions'] = Position::where('status', 1)->get();
        return view($this->layout . 'store', $data);
    }

    public function onEdit(Request $req)
    {
        $data['id'] = $req->id;
        $data['routeName'] = $this->routeName;
        $data['data'] = $this->table::find($req->id);
        $data['positions'] = Position::where('status', 1)->get();
        return view($this->layout . 'store', $data);
    }

    public function Save(StaffRequest $req, $id = "")
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
        $fileName = 'staff_export_' . date('Y-m-d_H-i-s') . '.csv';
        $staffs = $this->queryStaffs($req)->get();

        $headers = array(
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array(
            __('global.table.no'),
            __('global.table.name'),
            __('global.table.position'),
            __('global.table.phone'),
            __('global.table.email'),
            __('global.table.address'),
            __('global.table.created_at'),
        );

        $callback = function() use($staffs, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM
            fputcsv($file, $columns);

            foreach ($staffs as $index => $staff) {
                fputcsv($file, array(
                    $index + 1,
                    $staff->name ?? '',
                    $staff->position_title ?? ($staff->position?->title ?? ''),
                    $staff->phone_number ?? '',
                    $staff->email ?? '',
                    $staff->address ?? '',
                    $staff->created_at ? $staff->created_at->format('Y-m-d H:i:s') : '',
                ));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
