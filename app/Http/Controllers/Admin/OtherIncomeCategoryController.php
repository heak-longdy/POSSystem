<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OtherIncomeCategoryRequest;
use App\Models\OtherIncomeCategory;
use App\Services\Tools;
use Illuminate\Http\Request;

class OtherIncomeCategoryController extends Controller
{
    protected $layout = 'admin::pages.otherIncomeCategory.';
    private $tools;
    private $table = OtherIncomeCategory::class;
    private $routeName = "other-income-category";

    public function __construct(Tools $tools)
    {
        $this->tools = $tools;
    }

    public function index(Request $req)
    {
        $data['status'] = $req->status;
        $data['routeName'] = $this->routeName;

        if (!$req->status) {
            return redirect()->route('admin-' . $this->routeName . '-list', 1);
        }

        $query = $req->status !== 'trash'
            ? OtherIncomeCategory::where('status', $req->status)
            : OtherIncomeCategory::onlyTrashed();

        if ($req->filled('search')) {
            $search = trim($req->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $data['data'] = $query->orderBy('ordering', 'asc')->orderBy('id', 'desc')->paginate(50);
        $data['exportUrl'] = route('admin-' . $this->routeName . '-export', request()->query());

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
        $data['data'] = OtherIncomeCategory::find($req->id);
        $data['routeName'] = $this->routeName;
        return view($this->layout . 'store', $data);
    }

    public function Save(OtherIncomeCategoryRequest $req, $id = "")
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

    public function delete($id = "")
    {
        $category = OtherIncomeCategory::find($id);
        if ($category && $category->is_system) {
            return redirect()->back()->with('error', 'System categories cannot be deleted.');
        }
        return $this->tools->onDelete($this->table, $id);
    }

    public function destroy($id = "")
    {
        $category = OtherIncomeCategory::withTrashed()->find($id);
        if ($category && $category->is_system) {
            return redirect()->back()->with('error', 'System categories cannot be permanently purged.');
        }
        return $this->tools->onDestroy($this->table, $id);
    }
}
