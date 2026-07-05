<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\BaseWebSiteController;
use App\Models\ModelSetting;
use Illuminate\Http\Request;

class OurServiceController extends BaseWebSiteController
{
    protected $layout = 'website::pages.';
    public function ourService()
    {
        $dataQuery = ModelSetting::where('setting_type','OurService')->get()->groupBy('type');

        // Process data to set position and isBold
        foreach ($dataQuery as $category => &$services) {
            foreach ($services as $index => &$service) {
                $service['position'] = ($index % 2 === 0) ? "left" : "right";
                $service['isBold'] = ($service['position'] === "left");
            }
        }
        $data['data'] = $dataQuery;
        // return $data;
        return view($this->layout . 'ourService',$data);
    }
}
