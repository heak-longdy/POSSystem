<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Models\Service;
class ServiceController extends Controller
{
    protected $layout = 'admin::pages.service.';
    function __construct()
    {
        $this->middleware('permission:service-view', ['only' => ['index']]);
        $this->middleware('permission:service-create', ['only' => ['onCreate', 'onSave']]);
        $this->middleware('permission:service-update', ['only' => ['onEdit', 'onSave', 'onUpdateStatus', 'restore']]);
        $this->middleware('permission:service-delete', ['only' => ['delete', 'restore', 'destroy']]);
    }
    public function index(Request $req)
    {
        $data['status'] = $req->status;
        $search = $req->search ? $req->search : '';
        if (!$req->status) {
            return redirect()->route('admin-service-list', 1);
        }
        if ($req->status != 'trash') {
            $query = Service::where('status', $req->status);
        } else {
            $query = Service::onlyTrashed();
        }
        $data['data'] = $query->where(function ($q) use ($search) {
            if ($search) {
                $q->where('name', 'like', '%' . $search . '%');
                $q->orWhere('phone', 'like', '%' . $search . '%');
            }
        }) ->orderBy('ordering', 'desc')->paginate(50);

        return view($this->layout . 'index', $data);
    }
    public function onCreate(Request $req)
    {
        $data["data"] = Service::where('id', $req->id)->first();
        return view($this->layout . 'create',$data);
    }
    public function onSave(Request $req, $id = null)
    {
        $item = [
            "name" => $req->name,
            "price" => $req->price,
            "status" => 1,
            'ordering' => $req->ordering,
            'commission' => $req->commission,
            "image" =>  $req->image ?? $req->tmp_file ?? null,
        ];

        $req->validate([
            "name" => "required|unique:services,name" . ($id ? ",$id" : ''),
        ], [
            "name.unique" => "Name already exist",
        ]);

        DB::beginTransaction();
        try {
            $status = "Create success.";
            if ($id) {
                $data = Service::find($id);
                $data->update($item);
                $status = "Update success.";
            } else {
                $data = Service::create($item);
            }
            DB::commit();
            Session::flash('success', $status);
            return redirect()->route('admin-service-list', 1);
        } catch (\Exception $e) {
            DB::rollback();
            //dd($e->getMessage());
            Session::flash('warning', 'Create unsuccess!');
            return redirect()->back();
        }
    }
    public function onUpdateStatus(Request $req)
    {
        $statusGet = 'Enable';
        try {
            $data = Service::find($req->id);
            $data->update(['status' => $req->status]);
            if ($data->status !== '1') {
                $statusGet = 'Disable';
            }
            Session::flash('success', $statusGet);
            return redirect()->back();
        } catch (\Exception $error) {
            $status = false;
            return redirect()->back();
        }
    }
}
