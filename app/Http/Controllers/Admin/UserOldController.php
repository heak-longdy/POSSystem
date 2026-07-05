<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModelHasPermission;
use App\Models\ModulePermission;
use App\Models\Permission;
use App\Models\UploadFile;
use App\Models\User;
use App\Services\QueryService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class UserController extends Controller
{

    public function __construct(QueryService $queryService)
    {
        $this->middleware('permission:user-view', ['only' => ['index']]);
        $this->middleware('permission:user-create', ['only' => ['onCreate', 'onSave']]);
        $this->middleware('permission:user-update', ['only' => ['onCreate', 'onSave', 'onUpdateStatus', 'onRestore', 'onChangePassword', 'onSavePassword', 'setPermission', 'savePermission']]);
        $this->middleware('permission:user-delete', ['only' => ['onDelete']]);
        $this->queryService = $queryService;
    }

    public function login()
    {
        if (Auth::check()) {
            return redirect()->route('admin-dashboard');
        }
        return view("admin::auth.sign-in");
    }

    public function forgotPassword()
    {
        return view("admin::auth.forgot-password");
    }

    public function index(Request $req)
    {
        if (!$req->id) {
            return redirect()->route('admin-user-list', 1);
        }
        $data['data'] = User::when(filled(request('keyword')), function ($q) {
            $q->where(function ($q) {
                $q->where('name', 'like', '%' . request('keyword') . '%');
                $q->orWhere('phone', 'like', '%' . request('keyword') . '%');
                $q->orWhere('email', 'like', '%' . request('keyword') . '%');
            });
        })
            ->when(request('role'), function ($q) {
                $q->where('role', request('role'));
            })
            ->where('type', 'admin')
            ->where('role', '!=', config('dummy.user.role.super_admin'))
            ->where("status", $req->id)
            ->orderByDesc("created_at")
            ->paginate(50);

        return view("admin::pages.user.index", $data);
    }

    public function viewTrash(Request $req)
    {
        $data['id'] = $req->id;
        $data['data'] = User::onlyTrashed()
            ->when(filled(request('keyword')), function ($q) {
                $q->where('name', 'like', '%' . request('keyword') . '%')
                    ->orWhere('phone', 'like', '%' . request('keyword') . '%')
                    ->orWhere('email', 'like', '%' . request('keyword') . '%');
            })
            ->where('role', '!=', config('dummy.user.role.super_admin'))
            ->orderBy("created_at", "desc")
            ->paginate(10);

        return view("admin::pages.user.index", $data);
    }

    public function onCreate(Request $req)
    {
        $data["data"] = User::where('type', 'admin')->where('id', $req->id)->first();
        return view("admin::pages.user.create", $data);
    }

    public function onSave(Request $req)
    {
        $id = $req->id;
        $item = [
            "username" => $req->name,
            "email" => $req->email,
            "phone" => $req->phone,
            "status" => $req->status,
            "profile" =>  $req->image ?? $req->tmp_file ?? null,
            "remember_token" => $req->_token,
            "type" => "admin",
        ];

        $req->validate([
            "email" => "nullable|unique:users,email" . ($id ? ",$id" : ''),
            "phone" => "nullable|unique:users,phone" . ($id ? ",$id" : ''),
        ], [
            "email.unique" => "unique_email",
            "phone.unique" => "unique_phone",
        ]);
        $status = "Create success.";
        try {
            if (!$id) {
                $item["role"] = "admin";
                $item["password"] = bcrypt($req->password);
                User::create($item);
            } else {
                User::find($id)->update($item);
                $status = "Update success.";
            }
            Session::flash("success", $status);
            return redirect()->route("admin-user-list", 1);
        } catch (Exception $error) {
            dd($error);
            Session::flash('warning', 'Create unsuccess!');
            return redirect()->back();
        }
    }

    public function onChangePassword(Request $req)
    {
        $user = User::where('type', 'admin')->where('id', $req->id)->first();
        if ($user->role == 'super_admin') {
            return redirect()->route("admin-user-list", 1);
        }
        return view("admin::pages.user.change-password", ['data' => $user]);
    }

    public function onSavePassword(Request $req)
    {
        $item = [
            "password" => bcrypt($req->password),
        ];
        try {
            $user = User::find($req->id);
            $user->update($item);
            $status = "change password success";
            Session::flash("success", $status);
        } catch (Exception $error) {
            Session::flash("warning", "change password unsuccess");
        }
        return redirect()->route("admin-user-list", 1);
    }

    public function onUpdateStatus(Request $req)
    {
        $status = true;
        $item = [
            "status" => $req->status,
        ];
        try {
            $status = $req->status == 2 ? "Disable successful!" : "Enable successful!";
            User::where("id", $req->id)->update($item);
            Session::flash("success", $status);
        } catch (Exception $error) {
            Session::flash('warning', 'Create unsuccess!');
        }
        return redirect()->back();
    }

    public function onDelete(Request $req)
    {
        $status = "Delete successful!";
        try {
            if ($req->to_trash) {
                User::find($req->id)->delete();
            }
            $status = true;
        } catch (Exception $error) {
            $status = "Delete unsuccess!";
        }
        Session::flash("success", $status);
        return redirect()->back();
    }

    public function onRestore(Request $req)
    {
        $status = "Restore successful!";
        try {
            User::withTrashed()->find($req->id)->restore();
            Session::flash("success", $status);
        } catch (Exception $error) {
            $status = "Restore unsuccess!";
            Session::flash("warning", $status);
        }
        return redirect()->back();
    }

    public function setPermission()
    {
        // check user can't update yourself and super admin
        $user = User::find(request("id"));
        if ($user->role == "super_admin" || $user->id == Auth::user()->id) {
            return redirect()->back();
        }
        $data["user"] = $user;
        $data['ModulPermission'] = ModulePermission::select('parent_id')->groupBy('parent_id')->orderBy('sort_no')->get();
        $data['permission'] = $data["user"]->ModelHasPermission;
        return view("admin::pages.user.permission", $data);
    }

    public function savePermission(Request $req)
    {
        $req->validate([
            "permission" => "required",
        ], [
            "permission.required" => "Permission required",
        ]);
        if (!$req->permission) {
            return redirect()->back();
        }
        DB::beginTransaction();
        try {
            $data = User::find($req->id);
            $permissions = Permission::pluck('name')->toArray();
            $revoke = array_diff($permissions, $req->permission);
            $data->givePermissionTo($req->permission);
            $data->revokePermissionTo($revoke);
            DB::commit();
            Session::flash("success", 'Set permission successful!');
            return redirect()->route("admin-user-list", 1);
        } catch (Exception $error) {
            DB::rollback();
            $status = "Permission unsuccess!";
            Session::flash("warning", $status);
            return redirect()->back();
        }
    }
}
