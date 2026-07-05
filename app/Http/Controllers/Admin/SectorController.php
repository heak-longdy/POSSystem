<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SectorRequest;
use Illuminate\Http\Request;
use App\Services\Tools;
use App\Models\Sector;

class SectorController extends Controller
{
    protected $layout = 'admin::pages.sector.';
    private $tools;
    private $table = Sector::class;
    private $routeName = "sector";
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
            $query = $this->table::where('status', $req->status);
        } else {
            $query = $this->table::onlyTrashed();
        }
        $data['data'] = $query->with('Position')->where(function ($q) use ($search) {
            if ($search) {
                $q->where('name', 'like', '%' . $search . '%');
                $q->orWhere('phone', 'like', '%' . $search . '%');
            }
        })->orderBy('order', 'desc')->paginate(50);

        return view($this->layout . 'index', $data);
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
    public function Save(SectorRequest $req, $id = "")
    {
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
