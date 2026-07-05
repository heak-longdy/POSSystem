<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reward;
use App\Models\Product;
use App\Models\Service;
use App\Models\Promotion;
use App\Models\ShopProduct;
use App\Models\ShopService;
class RewardController extends Controller
{
    public function index()
    {
     // return date('Y-m-d');
        $barber = auth('barber-api')->user();
        $data = Reward::whereJsonContains('shop_id',["$barber->shop_id"])->where('status',1)
           ->whereDate('start_date', '<=', date('Y-m-d'))
           ->whereDate('end_date', '>=', date('Y-m-d'))
          ->get();
    	
        foreach($data as $index => $item){
          	$item->point = (int) $item->point;
            $item->product = $item->product_id?ShopProduct::whereIn('product_id',$item->product_id)->where('shop_id',$barber->shop_id)->get():[];
          	foreach($item->product as $value){
              $value->name = Product::where('id',$value->product_id)->first()->name;
              $value->type = 'product';
            }
            $item->service = $item->service_id?ShopService::whereIn('service_id',$item->service_id)->where('shop_id',$barber->shop_id)->get():[];
          foreach($item->service as $value){
            $value->name = Service::where('id',$value->service_id)->first()->name;
            $value->type = 'service';
          }
        }
        if(count($data) > 0){
            $message = true;
            $reward = $data;
        }else{
            $message = false;
            $reward = [];
        }
        return response()->json([
            'message' => true,
            'data' => $reward,
        ], 200);
    }
  public function reward(Request $req)
    {
       	$barber = auth('barber-api')->user();
        $data = Reward::whereJsonContains('shop_id',["$barber->shop_id"])->where('status',1)->get();
    	//$sService = ShopService::where('shop_id',$barber->shop_id)->pull('service_id')->get();
    	//$sProduct = ShopProduct::where('shop_id',$barber->shop_id)->pull('product_id')->get();
        foreach($data as $index => $item){
          	$item->point = (int) $item->point;
            $item->product = $item->product_id?ShopProduct::whereIn('product_id',$item->product_id)->get():[];
          	foreach($item->product as $value){
              $value->name = Product::where('id',$value->product_id)->first()->name;
              $value->type = 'product';
            }
            $item->service = $item->service_id?ShopService::whereIn('service_id',$item->service_id)->get():[];
          foreach($item->service as $value){
            $value->name = Product::where('id',$value->service_id)->first()->name;
            $value->type = 'service';
          }
        }
        if(count($data) > 0){
            $message = true;
            $reward = $data;
        }else{
            $message = false;
            $reward = [];
        }
        return response()->json([
            'message' => true,
            'data' => $reward,
        ], 200);
    }
  public function promotion()
    {
    	$barber = auth('barber-api')->user();
        $data = Promotion::whereJsonContains('shop_id',["$barber->shop_id"])->where('status',1)->first();
    	$product = ShopProduct::with(['product'])->where('shop_id',$barber->shop_id)->get();
        foreach($product as $value){
            $value->promotion = Promotion::whereJsonContains('shop_id',["$barber->shop_id"])->whereJsonContains('product_id',["$value->product_id"])->get();
        }
        return $product;
    }
}
