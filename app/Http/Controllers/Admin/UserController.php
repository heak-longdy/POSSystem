<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PermissionRequest;
use App\Http\Requests\Admin\ResetPasswordRequest;
use App\Http\Requests\Admin\UserRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\ModulePermission;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\Tools;
use App\Support\PermissionModuleSelection;
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
    private function queryUsers(Request $req)
    {
        $status = $req->status ?? 1;
        if ($status !== 'trash') {
            $query = $this->table::where('status', $status);
        } else {
            $query = $this->table::onlyTrashed();
        }

        if ($req->filled('role')) {
            $query->where('role', $req->role);
        }

        if ($req->filled('search')) {
            $search = trim($req->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                  ->orWhere('email', 'LIKE', '%' . $search . '%')
                  ->orWhere('phone', 'LIKE', '%' . $search . '%');
            });
        }

        return $query->where('role', '!=', 'super_admin')
            ->orderByDesc("created_at");
    }

    public function index(Request $req)
    {
        $data['status'] = $req->status;
        $data['routeName'] = $this->routeName;
        if (!$req->status) {
            return redirect()->route('admin-' . $this->routeName . '-list', 1);
        }

        $query = $this->queryUsers($req);
        $data['data'] = $query->paginate(50);
        $data['exportUrl'] = route('admin-user-export', request()->query());

        return view($this->layout . 'index', $data);
    }

    public function report(Request $req)
    {
        $users = $this->queryUsers($req)->get();
        $users->makeVisible(['created_at']);

        return response()->json([
            'status' => $req->status ?? 1,
            'rows'   => $users,
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
    public function Save(UserRequest $req, $id = "")
    {
        $user = $id ? User::findOrFail($id) : new User();
        $creating = !$user->exists;
        if ($req->password) {
            $req["password"] = bcrypt($req->password);
        } else {
            unset($req["password"]);
        }
        try {
            DB::transaction(function () use ($req, $user, $creating) {
                // Roles are not editable through the user details form.
                $user->fill($req->only(array_diff($user->getFillable(), ['role'])));
                if ($creating) {
                    $user->role = 'admin';
                }
                $user->save();

                if ($creating) {
                    // The Role field is a label. Attaching the shared Spatie admin
                    // role would also grant any permissions configured on that role.
                    $user->syncPermissions(['dashboard-view']);
                }
            });
        } catch (\Throwable $error) {
            Log::error('Unable to save user', ['user_id' => $id ?: null, 'exception' => $error]);
            return redirect()->back()->withInput($req->except(['password', 'confirm_password']))
                ->with('warning', __('Unable to save user.'));
        }

        Session::flash('success', $creating ? 'Create success.' : 'Update success.');
        return $req->input('save_opt') === 'save_new'
            ? redirect()->back()
            : redirect()->route('admin-user-list', 1);
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
            Session::flash("success", __('user.message.change_password_success'));
        } catch (Exception $error) {
            Session::flash("warning", __('user.message.change_password_error'));
        }
        return redirect()->route("admin-user-list", 1);
    }

    public function onPermission(Request $req, $id = null)
    {
        $userId = $id ?? $req->id;
        $user = User::findOrFail($userId);

        if ($user->role === 'super_admin' && (!Auth::check() || Auth::user()->role !== 'super_admin')) {
            Session::flash('warning', __('user.message.cannot_modify_super_admin'));
            return redirect()->route('admin-user-list', 1);
        }

        $modules = ModulePermission::with(['permission' => function ($query) {
                $query->where('guard_name', 'web')->orderBy('id');
            }])
            ->get();
        $permissionMenu = PermissionModuleSelection::treeFromMenu($modules);
        $modules = PermissionModuleSelection::flatten($permissionMenu);

        $userPermissions = $user->permissions->pluck('name')->toArray();
        $selectablePermissions = $modules->flatMap(fn ($module) => $module->permission->pluck('name'))->all();
        $preservedPermissions = array_values(array_diff($userPermissions, $selectablePermissions));
        $inheritedPermissions = $user->getPermissionsViaRoles()->pluck('name')->unique()->values()->all();

        return view($this->layout . 'permission', [
            'user' => $user,
            'permissionMenu' => $permissionMenu,
            'userPermissions' => $userPermissions,
            'preservedPermissions' => $preservedPermissions,
            'inheritedPermissions' => $inheritedPermissions,
        ]);
    }

    public function onSavePermission(PermissionRequest $req, $id = null)
    {
        $userId = $id ?? $req->id;
        $user = User::findOrFail($userId);

        if ($user->role === 'super_admin' && (!Auth::check() || Auth::user()->role !== 'super_admin')) {
            Session::flash('warning', __('user.message.cannot_modify_super_admin'));
            return redirect()->route('admin-user-list', 1);
        }

        try {
            $permissions = $req->validated()['permission'] ?? [];
            DB::transaction(function () use ($user, $permissions) {
                $user->syncPermissions($permissions);
            });
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
            Session::flash('success', __('user.message.permission_success'));

            if ($req->ajax() || $req->wantsJson()) {
                return response()->json([
                    'error' => false,
                    'message' => __('user.message.permission_success'),
                ]);
            }

            return redirect()->route('admin-user-list', 1);
        } catch (\Throwable $error) {
            Log::error('Unable to update user permissions', ['user_id' => $user->id, 'exception' => $error]);
            if ($req->expectsJson()) {
                return response()->json(['error' => true, 'message' => __('user.message.permission_error')], 500);
            }
            return redirect()->back()->withInput()->with('warning', __('user.message.permission_error'));
        }
    }

    public function export(Request $req)
    {
        $fileName = 'users_export_' . date('Y-m-d_H-i-s') . '.csv';
        $users = $this->queryUsers($req)->get();
        $users->makeVisible(['created_at']);

        $headers = array(
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array(
            __('user.table.no'),
            __('user.table.name'),
            __('user.table.email'),
            __('user.form.phone.label'),
            __('user.table.language'),
            __('user.table.role'),
            __('user.table.status'),
            __('user.table.post_date'),
        );

        $callback = function() use($users, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM
            fputcsv($file, $columns);

            foreach ($users as $index => $user) {
                $statusLabel = $user->status == 1 ? __('user.form.status.active') : __('user.form.status.disable');
                $roleKey = 'user.roles.' . $user->role;
                $roleLabel = __($roleKey);
                if ($roleLabel === $roleKey) {
                    $roleLabel = ucfirst(str_replace('_', ' ', $user->role ?? ''));
                }

                fputcsv($file, array(
                    $index + 1,
                    $user->name ?? '',
                    $user->email ?? '',
                    $user->phone ?? '',
                    $user->language_preference_label ?? '',
                    $roleLabel,
                    $statusLabel,
                    $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : '',
                ));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
