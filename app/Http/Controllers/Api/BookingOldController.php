<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use App\Models\Customer;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\User;
use App\Models\Shop;
use App\Models\Order;
use App\Models\Setting;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\Validator;
use App\Models\CommissionHistory;
use App\Models\Barber;
use App\Models\CustomerReward;
use Carbon\Carbon;
use App\Models\Reward;
use App\Models\CustomerPoint;
use App\Models\ShopProduct;
use App\Models\ShopService;

class BookingController extends Controller
{
  public function index(Request $req)
  {
    $barber = auth('barber-api')->user();

    $data_pending = Booking::where('barber_id', $barber->id)
      ->where('payment_status', 'Pending')
      ->limit(10)
      ->get();
    // return $data_pending;
    $data_paid = Booking::where('barber_id', $barber->id)
      ->where('payment_status', 'Paid')
      ->limit(10)
      ->get();

    $liability = Booking::where('payment_status', 'Pending')
      ->where('barber_id', $barber->id)
      //->whereDate('booking_date', '>=', $req->from_date)
      // ->whereDate('booking_date', '<=', $req->to_date)
      ->sum('total_price');

    $d = Booking::where('payment_status', 'Pending')
      ->where('barber_id', $barber->id)
      //->whereDate('booking_date', '>=', $req->from_date)
      // ->whereDate('booking_date', '<=', $req->to_date)
      ->sum('total_discount');

    $c = Booking::where('payment_status', 'Pending')
      ->where('barber_id', $barber->id)
      //->whereDate('booking_date', '>=', $req->from_date)
      // ->whereDate('booking_date', '<=', $req->to_date)
      ->sum('total_commission');

    $total = Booking::whereDate('booking_date', '>=', $req->from_date)
      ->whereDate('booking_date', '<=', $req->to_date)
      ->sum('total_price');

    $commission = Booking::where('barber_id', $barber->id)
      ->whereDate('booking_date', '>=', $req->from_date)
      ->whereDate('booking_date', '<=', $req->to_date)
      ->sum('total_commission');

    $unpaid = $liability - $d - $c;
    if (count($data_pending) > 0) {
      $message = true;
      $pending = $data_pending;
      $paid = $data_paid;
    } else {
      $message = false;
      $pending = [];
      $paid = [];
    }

    return response()->json([
      'message' => true,
      'pending' => $pending,
      'paid' => $paid,
      'liability' => $unpaid,
      'total' => $total,
      'commission' => $commission,
    ], 200);
  }
  public function store(Request $request)
  {
    // return explode(',', $request->price) ;
    // return explode(',', $request->reward_id) ;
    $validate = Validator::make($request->all(), [
      'customer_id' => 'required',
    ]);
    if ($validate->fails()) {
      return $validate->errors();
      // return response()->json([
      //     'errorMessage' => $validate->getMessageBag()
      // ], 202);
    }
    DB::beginTransaction();
    try {
      $no_num = '';
      $code = Booking::whereNotNull('invoice_number')->orderBy('invoice_number', 'desc')->first();
      if ($code) {
        $number = str_replace("NO-", "", $code->invoice_number);
        $numbers = str_pad($number + 1, 4, "0", STR_PAD_LEFT);  //00002
        $no_num = "NO-" . $numbers;
      } else {
        $no_num = "NO-0001";
      }
      $setting = Setting::first();
      $dataCustomer = Customer::find($request->customer_id);
      $total = $request->total_service_price + $request->total_product_price;
      $barber = auth('barber-api')->user();
      $shop = Shop::where('id', $barber->shop_id)->first();
      $book = new Booking();
      $book->invoice_number = $no_num;
      $book->payment_status = 'Pending';
      $book->shop_id = isset($barber->shop_id) && $barber->shop_id ? $barber->shop_id : null;
      $book->barber_id = $barber->id;
      $book->customer_id = $request->customer_id;
      $book->total_price = $total;
      $book->total_commission = $request->total_commission;
      $book->total_point = $request->total_point;
      $book->pay_way = $request->pay_way;
      $book->total_discount = $request->total_discount;
      //$book->discount_type = $request->discount_type;
      //$book->rate = $setting?$setting->rate:null;
      $book->booking_date = Carbon::now(); //date('Y-m-d H:i:s');
      $book->save();

      if ($request->total_point && isset($shop->brand_id)) {
        $customerService  = $request->service_id ? explode(',', $request->service_id) : [];
        $customerProduct  = $request->product_id ? explode(',', $request->product_id) : [];
        $count_of_using_service = count($customerService) + count($customerProduct);
        $c = CustomerPoint::where('customer_id', $request->customer_id)->where('brand_id', $shop->brand_id)->first();
        if ($c) {
          $c->total_point = $c->total_point + $request->total_point;
          $c->brand_id = $shop->brand_id;
          $c->shop_id = $barber->shop_id;
          $c->total_receving_point = (int)$c->total_receving_point + (int)$request->customer_use_point;
          $c->used_point = (int)$c->used_point + (int)$request->customer_use_point;
          $c->count_of_using_service = (int)$c->count_of_using_service + $count_of_using_service;
          $c->save();
        } else {
          $item = [
            "brand_id" => $shop->brand_id,
            "total_point" => $request->total_point,
            "customer_id" => $request->customer_id,
            "shop_id" => $barber->shop_id,
            "total_receving_point" => (int)$request->customer_use_point,
            "used_point" => (int)$count_of_using_service,
            "count_of_using_service" => $count_of_using_service
          ];
          CustomerPoint::create($item);
        }
      }
      if ($request->reward_id && isset($shop->brand_id)) {

        $reward_id  = explode(',', $request->reward_id);
        $reward_point  = explode(',', $request->reward_point);
        $reward_name  = explode(',', $request->reward_name);
        $reward_type  = explode(',', $request->reward_type);

        //$re = Reward::whereJsonContains('product_id',["$request->reward_id"])
        //->orWhereJsonContains('service_id',["$request->reward_id"])
        // ->sum('point');

        $customer = CustomerPoint::where('customer_id', $request->customer_id)->where('brand_id', $shop->brand_id)->first();
        $tpoint = $customer->total_point ? $customer->total_point : 0;
        $customer->total_point = $tpoint - $request->customer_use_point;
        $customer->save();

        for ($i = 0; $i < count($reward_id); $i++) {
          $rew = new CustomerReward;
          $rew->customer_id = $request->customer_id;
          $rew->booking_id = $book->id;
          $rew->reward_id =  $reward_id[$i];
          $rew->amount = $reward_point[$i];
          $rew->name = $reward_name[$i];
          $rew->type = $reward_type[$i];
          $rew->used_date = date('y-m-d');
          $rew->booking_id = $book->id;
          $rew->save();
        }
      }
      if ($request->service_id) {
        $service_id  = explode(',', $request->service_id);
        $p  = explode(',', $request->price);
        $discount  = explode(',', $request->service_discount);
        $discount_type  = explode(',', $request->service_discount_type);
        $service_commission = explode(',', $request->service_commission);
        $service_commission_type = explode(',', $request->service_commission_type);
        $remark = explode(',', $request->remark);


      //   if ($val->product_type == "service") {
      //     $product = ShopService::where('shop_id', $dataBooking->shop_id)->where('service_id', $val->product_id)->first();
      // } else {
      //     $product = ShopProduct::where('shop_id', $dataBooking->shop_id)->where('product_id', $val->product_id)->first();
      // }

        for ($i = 0; $i < count($service_id); $i++) {
          $shopService = $dataCustomer->phone != "999" ? ShopService::where('shop_id', $barber->shop_id)->where('service_id', $service_id[$i])->first() : null;
          $detail = new BookingDetail;
          $detail->booking_id = $book->id;
          $detail->service_id =  $service_id[$i];
          $detail->price =  $p[$i];
          $detail->qty =  1;
          $detail->service_discount =  $request->service_discount ? $discount[$i] : null;
          $detail->service_discount_type =  $request->service_discount_type ? $discount_type[$i] : null;
          $detail->point = isset($shopService) && $shopService ? $shopService->point : null;
          $detail->service_commission =  $request->service_commission ? $service_commission[$i] : null;
          $detail->service_commission_type =  $request->service_commission_type ? $service_commission_type[$i] : null;
          $detail->remark = $request->remark ? $remark[$i] : null;
          $detail->type = 'service';
          $detail->save();
        }
      }

      if ($request->product_id) {
        $product_id  = explode(',', $request->product_id);
        $qty  = explode(',', $request->qty);
        $price  = explode(',', $request->product_price);
        $discount  = explode(',', $request->product_discount);
        $discount_type  = explode(',', $request->product_discount_type);
        $product_commission = explode(',', $request->product_commission);
        $product_commission_type = explode(',', $request->product_commission_type);
        $remark = explode(',', $request->remark);
        for ($i = 0; $i < count($product_id); $i++) {
          $productService = $dataCustomer->phone != "999" ? ShopProduct::where('shop_id', $barber->shop_id)->where('product_id', $product_id[$i])->first() : null;
          $detail = new BookingDetail;
          $detail->booking_id = $book->id;
          $detail->product_id =  $product_id[$i];
          $detail->price =  $price[$i];
          $detail->qty =  $qty[$i];
          $detail->point = isset($productService) && $productService ? $productService->point : null;
          $detail->product_discount =  $request->product_discount ? $discount[$i] : null;
          $detail->product_discount_type =  $request->product_discount_type ? $discount_type[$i] : null;
          $detail->product_commission = $request->product_commission ? $product_commission[$i] : null;
          $detail->product_commission_type = $request->product_commission_type ? $product_commission[$i] : null;
          $detail->remark = $request->remark ? $remark[$i] : null;
          //$detail->rate = $setting?$setting->rate:null;
          $detail->type = 'product';
          $detail->save();
        }
      }
      $p_price = $request->total_product_price ? $request->total_product_price : 0;
      $p_serice = $request->total_service_price ? $request->total_service_price : 0;
      $total = $p_price + $p_serice;
      $item = [
        "amount" => $total,
        "shop_id" => $barber->shop_id,
        "barber_id" => $barber->id,
        'des' => $request->des,
        "commission_date" =>  date('Y-m-d H:i:s'),
      ];

      $commission = CommissionHistory::create($item);

      DB::commit();
      return response()->json([
        "message" => true,
        "error_message" => null
      ], 200);
    } catch (\Throwable $th) {
      DB::rollBack();
      return $th->getMessage();
    }
  }
  public function detail($id)
  {
    $booking = Booking::where('id', $id)->first();
    $detail = BookingDetail::with('service')->with('product')->where('booking_id', $booking->id)->get();
    $total = $booking ? $booking->total_price : 0;
    $c = Customer::where('id', $booking->customer_id)->first();
    $customer_name = $c ? $c->name : null;
    $customer_phone = $c ? $c->phone : null;
    $total_product_amount = BookingDetail::where('booking_id', $booking->id)->where('type', 'product')->sum('price');
    $total_service_amount = BookingDetail::where('booking_id', $booking->id)->where('type', 'service')->sum('price');
    $reward = CustomerReward::where('booking_id', $booking->id)->get();
    return response()->json([
      "message" => true,
      'name' => $customer_name,
      'phone' => $customer_phone,
      'point' => $booking->point ? $booking->point : 0,
      'booking_date' => $booking->booking_date ? $booking->booking_date : null,
      'payment_status' => $booking->payment_status ? $booking->payment_status : null,
      'booking_code' => $booking->invoice_number ? $booking->invoice_number : null,
      'total_price' => $booking->total_price ? $booking->total_price : null,
      'total_commission' => $booking->total_commission ? $booking->total_commission : null,
      'total_point' => $booking->total_point ? $booking->total_point : null,
      'total_discount' => $booking->total_discount ? $booking->total_discount : null,
      //'discount_type' => $booking->discount_type?$booking->discount_type:null,
      'reward' => $reward,
      'data' => $detail,
      'total' => $total,
      'total_product_amount' => $total_product_amount,
      'total_service_amount' => $total_service_amount,
      "error_message" => null
    ], 200);
    // with(['service'])->where('booking_id', $booking->id)->get();
  }
  public function payment(Request $request)
  {
    $barber = auth('barber-api')->user();
    $validate = Validator::make($request->all(), [
      'booking_id' => 'required',
    ]);
    if ($validate->fails()) {
      return $validate->errors();
    }

    try {
      DB::beginTransaction();
      if ($request->booking_id) {
        if ($request->total_price > $barber->wallet) {
          return response()->json([
            "message" => false,
            "error_message" => null,
          ], 200);
        }
        $booking_id  = explode(',', $request->booking_id);
        for ($i = 0; $i < count($booking_id); $i++) {
          $detail = Booking::where('id', $booking_id[$i])->first();
          $detail->payment_status = 'Paid';
          $detail->payment_date = date('y-m-d:h:s:i');
          $detail->save();
        }
        $wallet = Barber::where('id', $barber->id)->update([
          'wallet' => $barber->wallet - $request->total_price,
        ]);
      }
      DB::commit();
      return response()->json([
        "message" => true,
        "error_message" => null
      ], 200);
    } catch (\Throwable $th) {
      DB::rollBack();
      return $th;
    }
  }
  public function pending(Request $req)
  {
    $barber = auth('barber-api')->user();
    $data = Booking::where('barber_id', $barber->id)->where('payment_status', 'Pending')->paginate(20);
    if (count($data) > 0) {
      $message = true;
      $b = $data;
    } else {
      $message = false;
      $b = [];
    }

    return response()->json([
      'message' => true,
      'data' => $b,
    ], 200);
  }
  public function paid(Request $req)
  {
    $barber = auth('barber-api')->user();
    $data = Booking::where('barber_id', $barber->id)
      ->whereDate('booking_date', '>=', $req->from_date)
      ->whereDate('booking_date', '<=', $req->to_date)
      //->whereBetween('booking_date', [$req->from_date, $req->to_date])
      ->where('payment_status', 'Paid')->orderBy('booking_date', 'desc')->paginate(20);
    if (count($data) > 0) {
      $message = true;
      $b = $data;
    } else {
      $message = false;
      $b = [];
    }

    return response()->json([
      'message' => true,
      'data' => $b,
    ], 200);
  }
}
