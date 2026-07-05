<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogRequest;
use App\Models\Blog;
use Illuminate\Http\Request;
use App\Services\Tools;

class BlogController extends Controller
{
    protected $layout = 'admin::pages.blog.';
    private $tools;
    private $table = Blog::class;
    private $routeName = "blog";
    public function __construct(Tools $serTool)
    {
        $this->tools = $serTool;
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
        $data['data'] = $query->where(function ($q) use ($search) {
            if ($search) {
                $q->where('title', 'like', '%' . $search . '%');
                $q->orWhere('des', 'like', '%' . $search . '%');
            }
        })->orderBy('id', 'desc')->paginate(50);

        return view($this->layout . 'index', $data);
    }
    public function onCreate()
    {
        $data['routeName'] = $this->routeName;
        return view($this->layout . 'store',$data);
    }
    public function onEdit(Request $req)
    {
        $data['id'] = $req->id;
        $data['data'] = $this->table::find($req->id);
        $data['routeName'] = $this->routeName;
        return view($this->layout . 'store', $data);
    }
    public function Save(BlogRequest $req, $id = "")
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
