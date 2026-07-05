<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginMemberCarRequest;
use App\Models\FcmToken;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Shop;
use Illuminate\Support\Facades\Hash;
use App\Models\WalletHistory;
use App\Models\Barber;
use App\Models\CommissionHistory;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\Brand;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'password' => 'required',
        ], [
            'phone.required' => 'phone_required',
            'password.required' => 'password_required',
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }
        try {

            $check = auth()->guard('barber')->attempt(['phone' => $request->phone, 'password' => $request->password, 'status' => 1]);
            if (!$check) {
                return response()->json([
                    'message' => false,
                    'error' => "Phone number or password in correct",
                ], 401);
            }

            $barber = auth('barber')->user();
            $shop = Shop::where('id', $barber->shop_id)->first();
            if ($shop) {
                $brand = Brand::where('id', $shop->brand_id)->first();
            } else {
                $brand = null;
            }
            $token = $barber->createToken('authToken')->accessToken;
            $wallet = WalletHistory::where('shop_id', $barber->id);
            $pending = $wallet->where('status', 1)->sum('amount');
            $reject = $wallet->where('status', 3)->sum('amount');

            $net_earning = Booking::where('barber_id', $barber->id)->sum('total_commission');
            $id = Booking::where('barber_id', $barber->id)->select('id')->pluck('id');
            $c_product = BookingDetail::whereIn('booking_id', $id)->where('type', 'product')->sum('product_commission');
            $c_service = BookingDetail::whereIn('booking_id', $id)->where('type', 'service')->sum('service_commission');

            $dataUser = [
                'id' => $barber?->id,
                'phone' => $barber?->phone,
                'name' => $barber?->name,
                // 'nick_name' => $shop->nick_name,
                'image' => $barber?->image ? url('/file_manager' . $barber?->image) : null,
                'address' => $barber?->address,
                'total_wallet' => $barber->wallet,
                'pending_wallet' =>  $pending,
                'reject_wallet' =>  $reject,
                'total_earning' => $net_earning,
                'service_earning' => $c_service,
                'product_earning' => $c_product,
                'type' => $barber?->type,
                'shop_id' => $barber?->shop_id,
                'shop_name' => $shop ? $shop->name : null,
                'brand_name' => $brand ? $brand->name : null,
                'token' => $token,
                'is_point' => $barber?->is_point,
            ];
            return response()->json([
                'message' => true,
                'data' => $dataUser,
            ], 200);
        } catch (Exception $error) {
            return $error->getMessage();
        }
    }
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|unique:barbers',
            'password' => 'required',
            'name' => 'required',
            'code' => 'required',
        ], [
            'phone.required' => 'phone_required',
            'phone.unique' => 'phone_unique',
            'password.required' => 'password_required',
            'name.required' => 'username_required',
            'code.required' => 'Phone code required',
        ]);

        if ($validator->fails()) {
            return resFail($validator->errors());
        }
        DB::beginTransaction();
        try {
            $filename = null;
            if ($request->file('image')) {
                $profile = $request->file('image');
                $filename = '/' . time() . '.' . $request->file('image')->getClientOriginalExtension();
                $pathImg = public_path('file_manager');
                $profile->move($pathImg, $filename);
            }
            $barber = Barber::create([
                'phone' => $request->phone,
                'password' => bcrypt($request->password),
                'name' => $request->name,
                'address' => $request->address,
                'status' => 1,
                //'shop_id' => 17,
                'type' => 'staff',
                'code' => $request->code,
                'image' => $filename,
            ]);
            $token = $barber->createToken('authToken')->accessToken;
            DB::commit();
            auth()->guard('barber')->attempt(['phone' => $request->phone, 'password' => $request->password, 'status' => 1]);
            $wallet = WalletHistory::where('shop_id', $barber->id);
            $pending = $wallet->where('status', 1)->sum('amount');
            $reject = $wallet->where('status', 3)->sum('amount');

            $net_earning = Booking::where('barber_id', $barber->id)->sum('total_commission');
            $id = Booking::where('barber_id', $barber->id)->select('id')->pluck('id');
            $c_product = BookingDetail::whereIn('booking_id', $id)->where('type', 'product')->sum('product_commission');
            $c_service = BookingDetail::whereIn('booking_id', $id)->where('type', 'service')->sum('service_commission');

            $shop = Shop::where('id', $barber->shop_id)->first();
            if ($shop) {
                $brand = Brand::where('id', $shop->brand_id)->first();
            } else {
                $brand = null;
            }
            return response()->json([
                'message' => true,
                'data' => [
                    'id' => $barber?->id,
                    'phone' => $barber->phone,
                    'code' => $barber->code,
                    'name' => $barber->name,
                    //'nick_name' => $sh$barberop?->nick_name,
                    'total_wallet' => $barber->wallet,
                    'pending_wallet' =>  $pending,
                    'reject_wallet' =>  $reject,
                    'total_earning' => $net_earning,
                    'service_earning' => $c_service,
                    'product_earning' => $c_product,
                    'image' => $barber->image ? url('/file_manager' . $barber->image) : null,
                    'address' => $barber->address,
                    'type' => $barber?->type,
                    'shop_id' => $barber?->shop_id,
                    'shop_name' => $shop ? $shop->name : null,
                    'brand_name' => $brand ? $brand->name : null,
                    'token' => $token,
                ],
            ], 200);
        } catch (Exception $error) {
            DB::rollBack();
            return $error;
            return response()->json([
                'message' => false,
            ], 202);
        }
    }

    public function fcmToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'token' => 'required|unique:fcm_tokens,token'
        ], [
            'user_id.required' => 'user_required',
            'token.unique' => 'token_unique',
        ]);

        if ($validator->fails()) {
            return resFail($validator->errors());
        }
        DB::beginTransaction();
        try {
            FcmToken::create([
                'member_id' => $request->member_id,
                'token'   => $request->token
            ]);
            DB::commit();
            return response()->json([
                'message' => true,
            ], 200);
        } catch (Exception $error) {
            DB::rollBack();
            return response()->json([
                'message' => false,
            ], 202);
        }
    }

    public function checkPhone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
        ], [
            'phone.required' => 'password_required',
        ]);

        if ($validator->fails()) {
            return resFail($validator->errors());
        }
        $phone = Barber::where('phone', $request->phone)->select('id', 'name', 'phone', 'image')->first();
        $data = $phone ? $phone : null;
        $message = $phone ? true : false;
        $status = $phone ? 200 : 202;
        return response()->json([
            'data' => $data,
            'message' => $message,
        ], $status);
    }

    public function updateProfile(Request $request)
    {
        $barber = auth('barber-api')->user();
        $wallet = WalletHistory::where('shop_id', $barber->id);

        $pending = $wallet->where('status', 1)->sum('amount');
        $reject = $wallet->where('status', 3)->sum('amount');

        $net_earning = Booking::where('barber_id', $barber->id)->sum('total_commission');

        $id = Booking::where('barber_id', $barber->id)->select('id')->pluck('id');
        $c_product = BookingDetail::whereIn('booking_id', $id)->where('type', 'product')->sum('product_commission');
        $c_service = BookingDetail::whereIn('booking_id', $id)->where('type', 'service')->sum('service_commission');

        //$pending = $wallet->where('status',1)->sum('amount');
        //$reject = $wallet->where('status',3)->sum('amount');

        $shop = Shop::where('id', $barber->shop_id)->first();
        if ($shop) {
            $brand = Brand::where('id', $shop->brand_id)->first();
        } else {
            $brand = null;
        }
        try {
            $filename = null;
            if ($request->file('image')) {
                $profile = $request->file('image');
                $filename = '/' . time() . '.' . $request->file('image')->getClientOriginalExtension();
                $pathImg = public_path('file_manager');
                $profile->move($pathImg, $filename);
            }
            $barber->update([
                'name' => $request->name ?? $barber->name,
                'image' => $filename ?? $barber->image,
                'phone' => $request->phone ?? $barber->phone,
                // 'nick_name' => $barber?->nick_name,
                'address' => $request->address ?? $barber->address,
            ]);
            return response()->json([
                'message' => true,
                'data' => [
                    'id'    => $barber->id,
                    'name' => $barber?->name,
                    //'nick_name' => $barber?->nick_name,
                    'pending_wallet' =>  $pending,
                    'reject_wallet' =>  $reject,
                    'total_wallet' =>   $barber->wallet ? $barber->wallet : 0,
                    'pending_wallet' =>  $pending,
                    'reject_wallet' =>  $reject,
                    'total_earning' => $net_earning,
                    'service_earning' => $c_service,
                    'product_earning' => $c_product,

                    'phone' => $barber->phone,
                    'address' => $barber->address,
                    'type' => $barber?->type,
                    'shop_id' => $barber?->shop_id,
                    'shop_name' => $shop ? $shop->name : null,
                    'brand_name' => $brand ? $brand->name : null,
                    'image' => $barber->image ? url('/file_manager' . $barber->image) : null,
                ],
            ], 200);
        } catch (Exception $error) {
            return response()->json([
                'message' => false,
                'error' => $error->getMessage(),
            ], 202);
        }
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required',
            'current_password' => 'required',
        ], [
            'password.required' => 'Password Required',
            'current_password.required' => 'Current Password required',
        ]);

        if ($validator->fails()) {
            return resFail($validator->errors());
        }
        try {
            $barber = auth('barber-api')->user();
            if (!Hash::check($request->get('current_password'), $barber->password)) {
                return response()->json([
                    'message' => false,
                    'status' => 'Current Password is Invalid',
                ], 200);
            }

            $barber->update([
                'password' => bcrypt($request->password),
            ]);
            return response()->json([
                'message' => true,
                'status' => 'change_password_success',
            ], 200);
        } catch (Exception $error) {
            return response()->json([
                'message' => false,
                'status' => 'change_password_failed',
            ], $error);
        }
    }

    public function profile()
    {
        $barber = auth('barber-api')->user();
        $wallet = WalletHistory::where('shop_id', $barber->id);
        $pending = $wallet->where('status', 1)->sum('amount');
        $reject = $wallet->where('status', 3)->sum('amount');

        $net_earning = Booking::where('barber_id', $barber->id)->sum('total_commission');

        $id = Booking::where('barber_id', $barber->id)->select('id')->pluck('id');
        $c_product = BookingDetail::whereIn('booking_id', $id)->where('type', 'product')->sum('product_commission');
        $c_service = BookingDetail::whereIn('booking_id', $id)->where('type', 'service')->sum('service_commission');

        $shop = Shop::where('id', $barber->shop_id)->first();
        if ($shop) {
            $brand = Brand::where('id', $shop->brand_id)->first();
        } else {
            $brand = null;
        }

        return response()->json([
            'message' => true,
            'data' => [
                'id'    => $barber->id,
                'phone' => $barber?->phone,
                'name' => $barber?->name,
                //'nick_name' => $barber?->nick_name,
                'total_wallet' =>   $barber->wallet ? $barber->wallet : 0,
                //'total_wallet' => 100,
                'pending_wallet' =>  $pending,
                'reject_wallet' =>  $reject,
                'total_earning' => $net_earning,
                'service_earning' => $c_service,
                'product_earning' => $c_product,
                'image' => $barber?->image ? url('/file_manager' . $barber?->image) : null,
                'address' => $barber->address,
                'type' => $barber?->type,
                'shop_id' => $barber?->shop_id,
                'shop_name' => $shop ? $shop->name : null,
                'brand_name' => $brand ? $brand->name : null,
                'is_point' => $barber?->is_point,
            ],
        ], 200);
    }

    public function forgetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'password' => 'required',
        ], [
            'phone.required' => 'phone_required',
            'password.required' => 'password_required',
            'password.required' => 'password',
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }
        try {
            $user = Barber::where('phone', $request->phone)->first();
            if ($user) {
                $user->update(['password' => bcrypt($request->password)]);
                $message = true;
            } else {
                $message = false;
            }

            return response()->json([
                'message' => $message,
            ], 200);
        } catch (Exception $error) {
            return response()->json([
                'message' => false,
            ], 202);
        }
    }
    public function logOut(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
        ], [
            'token.required' => 'token_required',
        ]);

        if ($validator->fails()) {
            return resFail($validator->errors());
        }
        try {
            $AuthUser = auth('barber-api')->user();
            FcmToken::where('user_id', $AuthUser->id)->where('token', $request->token)->delete();
            $AuthUser->token()->revoke();
            return response()->json([
                'message' => true,
                'status' => 'logout_success',
            ], 200);
        } catch (Exception $error) {
            return response()->json([
                'message' => false,
                'status' => 'logout_failed',
            ], $error);
        }
    }

    public function deleteAccount()
    {
        DB::beginTransaction();
        try {
            $barber = auth('barber-api')->user();

            WalletHistory::where('barber_id', $barber->id)->delete();
            $bookingData = Booking::where('barber_id', $barber->id)->get();
            foreach ($bookingData as $book) {
                BookingDetail::where('booking_id', $book->id)->delete();
                $book->delete();
            }
            $barber->delete();
            DB::commit();
            return response()->json(['message' => 'success']);
        } catch (\Exception $e) {
            DB::rollback();
            Session::flash('warning', 'Create unsuccess!');
            return response()->json(['message' => 'error', 'error' => $e->getMessage()]);
        }
    }
}
