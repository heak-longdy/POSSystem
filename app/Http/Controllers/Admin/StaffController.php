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

    public function index(Request $req)
    {
        $data['status'] = $req->status;
        $data['routeName'] = $this->routeName;
        $search = $req->search ? $req->search : '';

        if (!$req->status) {
            return redirect()->route('admin-' . $this->routeName . '-list', 1);
        }

        if ($req->status != 'trash') {
            $query = $this->table::with('position')->where('status', $req->status);
        } else {
            $query = $this->table::with('position')->onlyTrashed();
        }

        if ($req->position_id) {
            $query->where('position_id', $req->position_id);
        }

        $data['data'] = $query->where(function ($q) use ($search) {
            if ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('phone_number', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            }
        })->orderBy('id', 'desc')->paginate(50);

        $data['positions'] = Position::where('status', 1)->get();

        return view($this->layout . 'index', $data);
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
}
