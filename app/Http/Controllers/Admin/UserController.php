<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PermissionRequest;
use App\Http\Requests\Admin\ResetPasswordRequest;
use App\Http\Requests\Admin\UserRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\ModelHasPermission;
use App\Models\ModulePermission;
use App\Models\Permission;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\Tools;
use Exception;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    protected $layout = 'admin::pages.user.';
    private $tools;
    private $table = User::class;
    private $routeName = "user";
    public function __construct(Tools $tools)
    {
        $this->tools = $tools;
    }
    public function login()
    {
        if (Auth::check()) {
            return redirect()->route('admin-dashboard');
        }
        return view("admin::auth.login");
        return view("admin::auth.sign-in");
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

        $data['data'] = $query->when($search, function ($q) {
            $q->where(function ($q, $search) {
                $q->where('name', 'like', '%' . $search . '%');
                $q->orWhere('email', 'like', '%' . $search . '%');
            });
        })
            ->when(request('role'), function ($q) {
                $q->where('role', request('role'));
            })
            ->where('role', '!=', 'super_admin')
            ->orderByDesc("created_at")
            ->paginate(50);
            

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
    public function Save(UserRequest $req, $id = "")
    {
        if ($req->password) {
            $req["password"] = bcrypt($req->password);
        } else {
            unset($req["password"]);
        }
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

    public function onChangePassword($id="")
    {
        $user = User::where('role', '!=', 'super_admin')->where('id', $id)->first();
        if ($user->role == 'super_admin') {
            return redirect()->route("admin-user-list", 1);
        }
        return view("admin::pages.user.change-password", ['data' => $user]);
    }
    public function onSavePassword(ResetPasswordRequest $req)
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
}
