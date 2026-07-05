<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\BaseWebSiteController;
use Illuminate\Http\Request;
use App\Models\ModelSetting;

class AboutController extends BaseWebSiteController
{
    protected $layout = 'website::pages.';
    public function about()
    {
        $data['data'] = ModelSetting::where('setting_type','About')->get()->groupBy('type');
        return view($this->layout . 'about',$data);
    }
}
