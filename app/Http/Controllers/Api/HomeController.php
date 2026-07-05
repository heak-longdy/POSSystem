<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\Request;
use App\Models\Slide;
use App\Models\ShopService;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Auth;
use App\Models\User;
use App\Models\ShopProduct;
use App\Models\Product;
use App\Models\Promotion;
use Exception;

class HomeController extends Controller
{
    public function getJob(){
      $data = Job::limit(2)->get();
      return response()->json([
        'status' => 200,
        'data' => $data,
        'message' => 'Jobs retrieved successfully',
    ]);
    }
    public function index2(Request $req){
      $a = $req->get('a');
      $b="fsfsf";
      try{
          
          return [
            'status' => 200,
            'mess' => "success",
            'fsfsf'=> $b
        ];
      }catch (Exception $e) {

        return [
            'status' => 500, // Use a proper error status code
            'mess' => "fail",
            'sss'=> $e->getMessage()
        ];
    }
     
    }

    public function index()
    {
        $barber = auth('barber-api')->user();
        $service = ShopService::with(['service'])->where('shop_id',$barber->shop_id)->where('status',1)->get();
        $product = ShopProduct::with(['product'])->where('shop_id',$barber->shop_id)->where('status',1)->get();
        $slide = Slide::where('status',1)->get();
      	foreach($service as $se){
          $se->service->commission = (float) $se->service['commission'];
          $p =  Promotion::whereJsonContains('shop_id',["$barber->shop_id"])
            ->whereJsonContains('service_id',["$se->service_id"])
            ->whereDate('from_date', '<=', date('Y-m-d'))
            ->whereDate('to_date', '>=', date('Y-m-d'))
            ->whereNull('customer_id')
            ->first();
          $se->discount = $p?$p->discount:null;
          $se->type = $p?$p->type:null;
        }
      	foreach($product as $pr){
          $pr->product->commission = (float) $pr->product['commission'];
          $p =  Promotion::whereJsonContains('shop_id',["$barber->shop_id"])->whereJsonContains('product_id',["$pr->product_id"])
             	->whereDate('from_date', '<=', date('Y-m-d'))
            	->whereDate('to_date', '>=', date('Y-m-d'))
            	->whereNull('customer_id')
            ->first();
          $pr->discount = $p?$p->discount:null;
          $pr->type = $p?$p->type:null;
        }
        return response()->json([
            'message' => true,
            'slide' => $slide,
            'service' => $service,
            'product' => $product,
        ], 200);
    }
  public function service()
    {
        $barber = auth('barber-api')->user();
        $service = ShopService::with(['service'])->where('shop_id',$barber->shop_id)->where('status',1)->paginate(20);
    	if(count ($service) > 0){
          foreach($service as $se){
          	$se->service->commission = (float) $se->service['commission'];
             $p =  Promotion::whereJsonContains('shop_id',["$barber->shop_id"])
               ->whereJsonContains('service_id',["$se->service_id"])
               ->whereNull('customer_id')
                ->whereDate('from_date', '<=', date('Y-m-d'))
            	->whereDate('to_date', '>=', date('Y-m-d'))
               ->first();
          	$se->discount = $p?$p->discount:null;
          	$se->type = $p?$p->type:null;
        }
          $message = true;
          $ser = $service; 
        }else{
          $message = false;
          $ser = [];
        }
        return response()->json([
            'message' => $message,
            'service' => $ser,
        ], 200);
    }
  public function product()
    {
        $barber = auth('barber-api')->user();
        $product = ShopProduct::with(['product'])->where('shop_id',$barber->shop_id)->where('status',1)->paginate(20);
    	if(count ($product) > 0){
          foreach($product as $pr){
          	$pr->product->commission = (float) $pr->product['commission'];
            $p =  Promotion::whereJsonContains('shop_id',["$barber->shop_id"])->whereJsonContains('product_id',["$pr->product_id"])
              ->whereNull('customer_id')
               	->whereDate('from_date', '<=', date('Y-m-d'))
            	->whereDate('to_date', '>=', date('Y-m-d'))
              ->first();
          	$pr->discount = $p?$p->discount:null;
          	$pr->type = $p?$p->type:null;
        	}
          $message = true;
          $pro = $product; 
        }else{
          $message = false;
          $pro = [];
        }
        return response()->json([
            'message' => $message,
            'product' => $pro,
        ], 200);
    }
  public function searchProduct(Request $req)
    {
        $shop = auth('barber-api')->user();
        $keyword = $req->keyword ? $req->keyword : '';

       $product =  ShopProduct::where('status',1)->whereHas('product', function ($query) use ($keyword){
        $query->where('name', 'like', '%'.$keyword.'%');
      })
      ->with(['product' => function($query) use ($keyword){
          $query->where('name', 'like', '%'.$keyword.'%');
      }])
        ->where('shop_id',$shop->shop_id)
        ->paginate(20);
        return $product;
        if(count ($product) > 0){
            $message = true;
            $pro = $product; 
          }else{
            $message = false;
            $ser = [];
          }
          return response()->json([
              'message' => $message,
              'product' => $pro,
          ], 200);
    }
    public function searchService(Request $req)
    {
        $shop = auth('barber-api')->user();
        $keyword = $req->keyword ? $req->keyword : '';

       $service =  ShopService::where('status',1)->whereHas('service', function ($query) use ($keyword){
        $query->where('name', 'like', '%'.$keyword.'%');
      })
      ->with(['service' => function($query) use ($keyword){
          $query->where('name', 'like', '%'.$keyword.'%');
      }])
        ->where('shop_id',$shop->shop_id)
        ->paginate(20);
        return $service;
        if(count ($service) > 0){
            $message = true;
            $se = $service; 
          }else{
            $message = false;
            $se = [];
          }
          return response()->json([
              'message' => $message,
              'service' => $se,
          ], 200);
    }
}
