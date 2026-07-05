<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\JobRequest;
use App\Models\Job;
use App\Models\JobList;
use Illuminate\Http\Request;
use App\Services\Tools;

class JobController extends Controller
{
    protected $layout = 'admin::pages.job.';
    private $tools;
    private $table = Job::class;
    private $routeName = "internships";
    public function __construct(Tools $serTool)
    {
        $this->tools = $serTool;
    }
    public function index(Request $req)
    {
        $data['status'] = $req->status;
        $data['routeName'] = $this->routeName;
        if (!$req->status) {
            return redirect()->route('admin-internships-list', 1);
        }
        if ($req->status != 'trash') {
            $query = Job::where('status', $req->status);
        } else {
            $query = Job::onlyTrashed();
        }
        $data['data'] = $query->orderBy('id', 'desc')->paginate(50);
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
        $data['data'] = Job::find($req->id);
        $data['routeName'] = $this->routeName;
        return view($this->layout . 'store', $data);
    }
    public function Save(JobRequest $req, $id = "")
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
