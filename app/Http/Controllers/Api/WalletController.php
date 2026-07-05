<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Models\WalletHistory;
use App\Models\Shop;
use Illuminate\Support\Facades\Validator;
use Auth;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Barber;
use App\Models\Setting;
class WalletController extends Controller
{
    public function index(Request $req)
    {
        $barber = auth('barber-api')->user();
     //return  $barber->shop_id;
        $data = WalletHistory::where('barber_id',$barber->id)
          ->whereBetween(DB::raw('DATE(status_date)'), [$req->from_date, $req->to_date])
          	->select('id','shop_id','amount','status','image','status_date','tran_id','tran_type','amount_dollar')->orderBy('id', 'desc')
  			->paginate(20);
      	if(count ($data) > 0){
          $message = true;
          $wallet = $data; 
        }else{
          $message = false;
          $wallet = [];
        }
        return response()->json([
            'message' => $message,
            'data' => $wallet,
          	'total_wallet' => (float) $barber->wallet,
          	'total_wallet_usd' =>  (float) $barber->wallet/4100,
        ], 200);
    }

    public function store(Request $req)
    {
      	$r = Setting::first();
      	if($r){
          $top_rate = $r->rate;
        }else{
          $top_rate = 4100;
        }
        $barber = auth('barber-api')->user();
      	$filename = null;
      	$kh = $req->amount * $top_rate;
        if ($req->file('image')) {
          $profile = $req->file('image');
          $filename = '/' . time() . '.' . $req->file('image')->getClientOriginalExtension();
          $pathImg = public_path('file_manager');
          $profile->move($pathImg, $filename);
        }
        $item = [
          	"tran_id" => $req->tran_id,
            "amount" => $kh,
          	'amount_dollar' => $req->amount,
            "shop_id" => $barber->shop_id,
          	"barber_id" => $barber->id,
            'status' => 2,
            "image" =>  $filename,
          	'status_date' => Carbon::now(),
          	"tran_type" => $req->tran_type,
        ];

        $validator = Validator::make($req->all(), [
            'amount' => 'required',
        ], [
            'amount.required' => 'Pls enter amount',
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }
		
        DB::beginTransaction();
        try {
          
            $data = WalletHistory::create($item);
          	$s = Barber::find($barber->id);
            $s->update(['wallet' => $s->wallet + $kh]);
            DB::commit();
            return response()->json([
                'message' => true,
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
           // dd($e->getMessage());
            return response()->json([
                'message' => false,
                'error' => $e->getMessage(),
            ], 200);
        }
    }
}
