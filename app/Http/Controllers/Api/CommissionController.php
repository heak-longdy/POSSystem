<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CommissionHistory;
use DB;
class CommissionController extends Controller
{
    public function index(Request $req)
    {
        $barber = auth('barber-api')->user();
        $data = CommissionHistory::where('barber_id',$barber->id)
          ->whereBetween(DB::raw('DATE(commission_date)'), [$req->from_date, $req->to_date])
          ->paginate(20);

        return response()->json([
            'message' => true,
            'data' => $data,
        ], 200);
    }
}
