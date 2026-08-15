<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Services\Tools;
use App\Http\Requests\Admin\CategoryRequest;
class CategoryController extends Controller
{
    protected $layout = 'admin::pages.category.';
    private $tools;
    private $table = Category::class;
    private $routeName = "category";
    public function __construct(Tools $serTool)
    {
        $this->tools = $serTool;
    }
    public function index(Request $req)
    {
        $data['status'] = $req->status;
        $data['routeName'] = $this->routeName;
        if (!$req->status) {
            return redirect()->route('admin-'.$this->routeName.'-list', 1);
        }
        if ($req->status != 'trash') {
            $query = Category::where('status', $req->status);
        } else {
            $query = Category::onlyTrashed();
        }
        $data['data'] = $query->orderBy('id', 'desc')->paginate(50);
        $data['exportUrl'] = route('admin-category-export');
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
        $data['data'] = Category::find($req->id);
        $data['routeName'] = $this->routeName;
        return view($this->layout . 'store', $data);
    }
    public function Save(CategoryRequest $req, $id = "")
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
        $fileName = 'categories_export_' . date('Y-m-d_H-i-s') . '.csv';
        $categories = Category::all(); // You might want to apply filters here if needed

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('ID', 'Name', 'Phone', 'Email', 'Address', 'Created At');

        $callback = function() use($categories, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($categories as $category) {
                $row['ID']  = $category->id;
                $row['Name']    = $category->name;
                $row['Phone']    = $category->phone;
                $row['Email']  = $category->email;
                $row['Address']  = $category->address;
                $row['Created At']  = $category->created_at;

                fputcsv($file, array($row['ID'], $row['Name'], $row['Phone'], $row['Email'], $row['Address'], $row['Created At']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
