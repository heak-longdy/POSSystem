<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OurServiceRequest;
use App\Models\OurService;
use App\Models\OurServiceDe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OurServiceController extends Controller
{
    protected $layout = 'admin::pages.our_service.';
    public function index()
    {
        $data['data'] = OurService::with('OurServiceDe')->get()->groupBy('type');
        return view($this->layout . 'index', $data);
    }
    // public function onStore(OurServiceRequest $req)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $itemReq = $req->all();
    //         $item["user"] = Auth::user()->id;
    //         $itemOurService = [
    //             "title" => $itemReq['title'],
    //             "image" => $itemReq['image']
    //         ];
    //         $OurService = OurService::updateOrCreate(['id' => $itemReq['id']], $itemOurService);
    //         if (isset($itemReq['details']) and $itemReq['details']) {
    //             foreach ($itemReq['details'] as $val) {
    //                 $itemOurServiceDe = [
    //                     "our_service_id" => $OurService?->id,
    //                     "title" => $val['ser_title'],
    //                     "description" => $val['ser_des']
    //                 ];
    //                 if ($val['ser_title']) {
    //                     $OurServiceDe = OurServiceDe::updateOrCreate(['id' => $val['ser_id']], $itemOurServiceDe);
    //                     $val['ser_id'] = $OurServiceDe->id;
    //                 }
    //             }
    //         }
    //         DB::commit();
    //         return response()->json(['message' => 'success', 'data' => $itemReq]);
    //     } catch (\Exception $e) {
    //         DB::rollback();
    //         return response()->json(['message' => 'error', 'error' => $e->getMessage()]);
    //     }
    // }
    public function onStore(OurServiceRequest $req)
    {
        try {
            // Use DB::transaction with a closure
            return DB::transaction(function () use ($req) {
                $itemReq = $req->all();
                dd($itemReq);
                $item["user"] = Auth::user()->id;

                // Handle OurService creation or update
                $itemOurService = [
                    "title" => $itemReq['title'],
                    "status" => $itemReq['status'],
                    "image" => $itemReq['image']
                ];
                $OurService = OurService::updateOrCreate(['id' => $itemReq['id']], $itemOurService);
                $serviceDetails = [];
                // Handle OurServiceDe details creation or update
                if (isset($itemReq['details']) && $itemReq['details']) {
                    foreach ($itemReq['details'] as $val) {
                        $itemOurServiceDe = [
                            "our_service_id" => $OurService?->id,
                            "title" => $val['ser_title'],
                            "description" => $val['ser_des']
                        ];

                        // $serviceDetailId[] = $val;

                        $OurServiceDe = OurServiceDe::updateOrCreate(['id' => $val['ser_id']], $itemOurServiceDe);
                        // Store the updated or newly created ID in the array
                        $serviceDetails[] = $OurServiceDe;
                    }
                }
                // Return success response
                return response()->json([
                    'message' => 'success',
                    'details' => $serviceDetails,
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
}
