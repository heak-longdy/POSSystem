<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\BaseWebSiteController;
use Illuminate\Http\Request;
use App\Models\Blog;
use App\Models\Job;

class BlogController extends BaseWebSiteController
{
    protected $layout = 'website::pages.';
    public function blog(Request $req)
    {
        $data['data'] = Blog::orderBy('id', 'desc')->paginate(15);
        return view($this->layout . 'blogs', $data);
    }
    public function blogDetail($id = "")
    {
        $data['data'] = Blog::find($id);
        $data['blogRelates'] = Blog::limit(3)->orderBy('id', 'desc')->get();
        $data['jobRelates'] = Job::limit(3)->orderBy('id', 'desc')->get();
        return view($this->layout . 'blogDetail', $data);
    }
}
