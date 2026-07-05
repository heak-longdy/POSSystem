<?php

namespace App\Http\Controllers\website;

use App\Http\Controllers\BaseWebSiteController;
use App\Models\Job;
use Illuminate\Http\Request;
use App\Models\UploadFile;
use Yajra\DataTables\DataTables;
use App\Models\DataTable;
use App\Models\JobList;
use App\Models\Partner;
use App\Models\Testimonial;

class HomeController extends BaseWebSiteController
{
    protected $layout = 'website::pages.';
    public function index(Request $req)
    {
        $data['jobs'] = Job::limit(5)->where('status',1)->orderBy('id', 'desc')->get();
        $data['testimonials'] = Testimonial::limit(5)->where('status',1)->orderBy('id', 'asc')->get();
        $data['partners'] = Partner::where('status',1)->orderBy('id', 'asc')->get();

        return view($this->layout . 'index', $data);
    }    
    public function verifyDoc(){
        return view($this->layout . 'VerifyDocument');
    }
}
