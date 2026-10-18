<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PositionRequest;
use App\Models\Position;
use Illuminate\Http\Request;
use App\Services\Tools;

class PositionController extends Controller
{
    protected $layout = 'admin::pages.position.';
    private $tools;
    private $table = Position::class;
    private $routeName = "position";
    public function __construct(Tools $tools)
    {
        $this->tools = $tools;
    }

    private function queryPositions(Request $req)
    {
        $status = $req->status ?? 1;
        if ($status !== 'trash') {
            $query = Position::where('status', $status);
        } else {
            $query = Position::onlyTrashed();
        }

        if ($req->filled('search')) {
            $search = trim($req->search);
            $query->where('title', 'LIKE', '%' . $search . '%');
        }

        return $query->orderBy('order', 'desc')->orderBy('id', 'desc');
    }

    public function index(Request $req)
    {
        $data['status'] = $req->status;
        $data['routeName'] = $this->routeName;
        if (!$req->status) {
            return redirect()->route('admin-'.$this->routeName.'-list', 1);
        }
        $query = $this->queryPositions($req);
        $data['data'] = $query->paginate(50);
        $data['exportUrl'] = route('admin-position-export', request()->query());

        return view($this->layout . 'index', $data);
    }

    public function report(Request $req)
    {
        $positions = $this->queryPositions($req)->get();

        return response()->json([
            'status' => $req->status ?? 1,
            'rows'   => $positions,
        ]);
    }
    public function onCreate()
    {
        return view($this->layout . 'store');
    }
    public function onEdit(Request $req)
    {
        $data['id'] = $req->id;
        $data['data'] = $this->table::find($req->id);
        return view($this->layout . 'store', $data);
    }
    public function Save(PositionRequest $req, $id = "")
    {
        return $this->tools->onSave($this->table, $req, $id, $this->routeName, null, [
            'success' => __($id ? 'position.message.update_success' : 'position.message.create_success'),
            'warning' => __('position.message.save_failed'),
        ]);
    }
    public function updateStatus($id,$status)
    {
        return $this->tools->onUpdateStatus($this->table, $id, $status, [
            'success' => __($status == 2 ? 'position.message.disable_success' : 'position.message.enable_success'),
            'warning' => __('position.message.status_failed'),
        ]);
    }
    public function restore($id = "")
    {
        return $this->tools->onRestore($this->table, $id, [
            'success' => __('position.message.restore_success'),
            'warning' => __('position.message.restore_failed'),
        ]);
    }
    public function destroy($id = "")
    {
        return $this->tools->onDestroy($this->table, $id, [
            'success' => __('position.message.delete_success'),
            'warning' => __('position.message.delete_failed'),
        ]);
    }

    public function delete($id = "")
    {
        return $this->tools->onDelete($this->table, $id, [
            'success' => __('position.message.delete_success'),
            'warning' => __('position.message.delete_failed'),
        ]);
    }

    public function export(Request $req)
    {
        $fileName = 'positions_export_' . date('Y-m-d_H-i-s') . '.csv';
        $positions = $this->queryPositions($req)->get();

        $headers = array(
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array(
            __('global.table.no'),
            __('position.form.name.label'),
            __('global.table.created_at'),
        );

        $callback = function() use($positions, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM
            fputcsv($file, $columns);

            foreach ($positions as $index => $position) {
                fputcsv($file, array(
                    $index + 1,
                    $position->title ?? '',
                    $position->created_at ? $position->created_at->format('Y-m-d H:i:s') : '',
                ));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
