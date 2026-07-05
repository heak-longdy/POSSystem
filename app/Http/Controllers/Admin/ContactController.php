<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contact;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
class ContactController extends Controller
{
    protected $layout = 'admin::pages.page.';
    function __construct()
    {
        $this->middleware('permission:contact-view', ['only' => ['Page']]);
    }
    public function index()
    {
        $data['data'] = Contact::first();
        return view($this->layout . 'contact', $data);
    }
    public function store(Request $req)
    {
        DB::beginTransaction();
        try {
            Contact::updateOrCreate(['id' => $req->id], $req->all());
            $status = "Update success.";
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
