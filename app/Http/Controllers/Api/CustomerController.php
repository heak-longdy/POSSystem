<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Customer;
use Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\CustomerDiscount;
use App\Models\CustomerPoint;
use App\Models\BrandSetting;
use App\Models\Shop;
use App\Models\Promotion;
use App\Models\ShopProduct;
use App\Models\Product;
use App\Models\ShopService;
use App\Models\Service;
class CustomerController extends Controller
{
    public function index(Request $req)
    {
      //return date('Y-m-d');
      	$barber = auth('barber-api')->user();
      	$shop = Shop::where('id',$barber->shop_id)->first();
        $search = $req->search ? $req->search : '';
        $query = Customer::orderBy('id','asc');
        $data = $query->where(function ($q) use ($search) {
            if ($search) {
                $q->where('phone', 'like', '%' . $search . '%');
            }
        })->paginate(20);
      	if(count ($data) > 0){
          foreach($data as $value){
            $p = Promotion::whereJsonContains('shop_id',["$barber->shop_id"])
              ->whereJsonContains('customer_id',["$value->id"])
              ->whereDate('from_date', '<=', date('Y-m-d'))
            	->whereDate('to_date', '>=', date('Y-m-d'))
              ->first();
            //$value->dd = $p;
            $value->product = $p?ShopProduct::where('shop_id',$barber->shop_id)->whereIn('product_id',$p->product_id)->get():[];
            $value->service = $p?ShopService::where('shop_id',$barber->shop_id)->whereIn('service_id',$p->service_id)->get():[];
            foreach($value->product as $product){
              $pro = Promotion::whereJsonContains('product_id',["$product->product_id"])->first();
              $product->name = Product::where('id',$product->product_id)->first()->name;
              $product->discount = $pro?$pro->discount:null;
              $product->type = $pro?$pro->type:null;
            }
            foreach($value->service as $service){
              $pro = Promotion::whereJsonContains('service_id',["$service->service_id"])->first();
              $service->name = Service::where('id',$service->service_id)->first()->name;
              $service->discount = $pro?$pro->discount:null;
              $service->type = $pro?$pro->type:null;
            }
            $setting = BrandSetting::where('brand_id',$shop->brand_id)->where('status',1)->orWhere('status',0)->where('brand_id',$shop->brand_id)->first();
            //$brandId = 
            $p = CustomerPoint::where('customer_id',$value->id)
              //->where('shop_id',$barber->shop_id)
             	->whereIn('brand_id',$setting->brand_point_use)
              ->sum('total_point',$setting);
            $value->total_point = (int) $p;  //$value-,>total_point;
            //$value->discount = CustomerDiscount::where('customer_id',$value->id)->first();
          }
          $message = true;
          $customer = $data; 
        }else{
          $message = false;
          $customer = [];
        }
        return response()->json([
            'message' => $message,
            'data' => $customer,
        ], 200);
    }
    public function onSave(Request $req)
    {
        $item = [
            "phone" => $req->phone,
        ];
        $validator = Validator::make($req->all(), [
            "phone" => "required|unique:customers,phone",
        ], [
            'phone.required' => 'Pls enter phone number',
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }

        DB::beginTransaction();
        try {
            $data = Customer::create($item);
            DB::commit();
            return response()->json([
                'message' => true,
              	'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => false,
            ], 200);
        }
    }
}
