<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AboutRequest;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\Admin\OurServiceRequest;
use App\Models\ModelSetting;
use App\Models\ModelSettingDe;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
class SettingController extends Controller
{
    protected $layout = 'admin::pages.';

    public function toolStore($req){
        try {
            // Use DB::transaction with a closure
            return DB::transaction(function () use ($req) {
                $itemReq = $req->all();
                $item["user"] = Auth::user()->id;

                // Handle OurService creation or update
                $itemOurService = [
                    "title" => $itemReq['title'],
                    "status" => $itemReq['status'],
                    "image" => $itemReq['image']
                ];
                $OurService = ModelSetting::updateOrCreate(['id' => $itemReq['id']], $itemOurService);
                $ModelSettingDetails = [];
                // Handle OurServiceDe details creation or update
                if (isset($itemReq['details']) && $itemReq['details']) {
                    foreach ($itemReq['details'] as $val) {
                        $itemOurServiceDe = [
                            "our_service_id" => $OurService?->id,
                            "title" => $val['ser_title'],
                            "description" => $val['ser_des']
                        ];

                        $ModelSettingDe = ModelSettingDe::updateOrCreate(['id' => $val['ser_id']], $itemOurServiceDe);
                        // Store the updated or newly created ID in the array
                        $ModelSettingDetails[] = $ModelSettingDe;
                    }
                }
                // Return success response
                return response()->json([
                    'message' => 'success',
                    'details' => $ModelSettingDetails,
                    'id'   => $OurService?->id,
                    'title' => $OurService?->title,
                    'status' => $OurService?->status,
                    'image' => $OurService?->image,
                    'type'  => $OurService?->type
                ]);
            });
        } catch (\Exception $e) {
            // Rollback is automatically handled in case of an exception, but you can still catch the error.
            return response()->json([
                'message' => 'error',
                'error' => $e->getMessage() // Catch the error message here
            ], 500); // Optionally, you can send a 500 (Internal Server Error) status code.
        }
    }

    public function indexOurService()
    {
        $data['data'] = ModelSetting::with('ModelSettingDe')->where('setting_type','OurService')->get()->groupBy('type');
        return view($this->layout . 'our_service.index', $data);
    }
    public function onStoreOurService(OurServiceRequest $req)
    {
        return $this->toolStore($req);
    }

    public function indexAboutUs()
    {
        $data['data'] = ModelSetting::with('ModelSettingDe')->where('setting_type','About')->get()->groupBy('type');
        return view($this->layout . 'about.index', $data);
    }
    public function onStoreAboutUs(AboutRequest $req)
    {
        return $this->toolStore($req);
    }
    
}
