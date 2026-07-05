<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TestimonialRequest;
use App\Models\Testimonial;
use App\Services\Tools;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    protected $layout = 'admin::pages.testimonial.';
    private $tools;
    private $table = Testimonial::class;
    private $routeName = "testimonial";

    public function __construct(Tools $serTool)
    {
        $this->tools = $serTool;
    }
    public function index(Request $req)
    {

        $data['status'] = $req->status;
        $data['routeName'] = $this->routeName;
        if (!$req->status) {
            return redirect()->route('admin-' . $this->routeName . '-list', 1);
        }
        if ($req->status != 'trash') {
            $query = $this->table::where('status', $req->status);
        } else {
            $query = $this->table::onlyTrashed();
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
        $data['data'] = $this->table::find($req->id);
        $data['routeName'] = $this->routeName;
        return view($this->layout . 'store', $data);
    }
    public function Save(TestimonialRequest $req, $id = "")
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
