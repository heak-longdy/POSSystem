<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api as Api;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\CommissionController;
use App\Http\Controllers\Api\RewardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('home-page', [Api\HomeController::class, 'index']);
Route::get('page', [Api\PageController::class, 'index']);
Route::get('contact', [Api\PageController::class, 'contact']);
Route::get('country', [CountryController::class, 'index']);
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::get('check-phone', [AuthController::class, 'checkPhone']);
Route::post('forget-password', [AuthController::class, 'forgetPassword']);


Route::get('home-page-test', [Api\HomeController::class, 'index2']);
Route::get('getJob', [Api\HomeController::class, 'getJob']);

Route::middleware('auth:barber-api')
    ->group(function () {
        Route::get('profile', [AuthController::class, 'profile']);
        Route::post('update-profile', [AuthController::class, 'updateProfile']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
         //deleteBarberAccount
         Route::delete('delete-account', [AuthController::class, 'deleteAccount']);
      
      	Route::get('page', [PageController::class, 'index']);


    //Wallet
    Route::get('wallet', [WalletController::class, 'index']);
    Route::post('store-wallet', [WalletController::class, 'store']);
    
    // Customer
    Route::get('customer', [CustomerController::class, 'index']);
    Route::post('store-customer', [CustomerController::class, 'onSave']);

    /// Earning 
    
    Route::get('earning', [BookingController::class, 'index']);

    Route::get('home', [HomeController::class, 'index']);
    Route::post('store-booking', [BookingController::class, 'store']);
    Route::get('service', [HomeController::class, 'service']);
    Route::get('product', [HomeController::class, 'product']);
      
    Route::get('search-product', [HomeController::class, 'searchProduct']);
    Route::get('search-service', [HomeController::class, 'searchService']);
    
    Route::get('barber-commission', [CommissionController::class, 'index']);
    Route::get('detail/{id}', [BookingController::class, 'detail']);
      
    Route::post('payment', [BookingController::class, 'payment']);
    Route::get('pending', [BookingController::class, 'pending']);
   	Route::get('paid', [BookingController::class, 'paid']);
    Route::get('reward', [RewardController::class, 'index']);
    Route::get('test', [RewardController::class, 'reward']);
      
});