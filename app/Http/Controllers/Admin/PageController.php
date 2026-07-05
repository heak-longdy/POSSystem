<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageRequest;
use App\Models\Page;
use App\Services\PageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class PageController extends Controller
{
    protected $layout = 'admin::pages.page.';
    private $PageService;
    function __construct(PageService $itemSer)
    {
        $this->middleware('permission:contact-view', ['only' => ['Page']]);
        $this->middleware('permission:about-view', ['only' => ['Page']]);
        $this->middleware('permission:policy-view', ['only' => ['Page']]);
        $this->middleware('permission:preface-view', ['only' => ['Page']]);
        $this->middleware('permission:supportUs-view', ['only' => ['Page']]);
        $this->PageService = $itemSer;
    }
    public function Page(Request $req)
    {
        if (!$req->type) {
            return redirect()->route('admin-dashboard');
        }
        $data['data'] = Page::where('type', $req->type)->first();
        return view($this->layout . $req->type, $data);
    }

    public function onSave(Request $req)
    {
        $req->type = $req->type == 'term-condition' ? 'term_condition' : $req->type;
        $data = Page::where('type', $req->type)->where('id', $req->id)->first();
        $items = [
            'title' => $req->title,
            'content' => $req->content,
            'type' => $req->type,
            'image' => $req->image,
        ];

        DB::beginTransaction();
        try {
            $status = "Create success.";
            if ($req->id && $data) {
                $data->update($items);
                $status = "Update success.";
            } else {
                $data = $data->create($items);
            }
            DB::commit();
            Session::flash('success', $status);
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollback();
            Session::flash('warning', 'Create unsuccess!');
            return redirect()->back();
        }
    }
}
