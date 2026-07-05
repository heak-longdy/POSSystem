<?php

use App\Services\FileManager;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Admin as Admin;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\CustomerPaidController;
use App\Http\Controllers\Admin\JobController;
use App\Http\Controllers\Admin\PartnerController;
use App\Http\Controllers\Admin\PlacementTypeController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\SectorController;
use App\Http\Controllers\Admin\TestimonialController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// Auth

// app/helpers.php
if (!function_exists('CRUD')) {
    function CRUD($Controller, $routeName)
    {
        Route::group([
            'prefix' => $routeName,
            'as'     => $routeName . '-'
        ], function () use ($Controller) {
            Route::get('list/{status?}', [$Controller, 'index'])->name('list');
            Route::get('create', [$Controller, 'onCreate'])->name('create');
            Route::get('edit/{id?}', [$Controller, 'onEdit'])->name('edit');
            Route::post('save/{id?}', [$Controller, 'Save'])->name('save');
            Route::match(['get', 'post'], 'status/{id}/{status}', [$Controller, 'updateStatus'])->name('status');
            Route::post('delete/{id?}', [$Controller, 'delete'])->name('delete');
            Route::post('restore/{id?}', [$Controller, 'restore'])->name('restore');
            Route::post('destroy/{id?}', [$Controller, 'destroy'])->name('destroy');

            Route::get('change-password/{id?}', [$Controller, 'onChangePassword'])->name('change-password');
            Route::post('save-password/{id?}', [$Controller, 'onSavePassword'])->name('save-password');

            Route::get('permission/{id?}', [$Controller, 'onPermission'])->name('permission');
            Route::post('save-permission/{id?}', [$Controller, 'onSavePermission'])->name('save-permission');
        });
    }
}


// function CRUD($Controller, $routeName)
// {
//     Route::group([
//         'prefix' => $routeName,
//         'as'     => $routeName . '-'
//     ], function () use ($Controller) {
//         Route::get('list/{status?}', [$Controller, 'index'])->name('list');
//         Route::get('create', [$Controller, 'onCreate'])->name('create');
//         Route::get('edit/{id?}', [$Controller, 'onEdit'])->name('edit');
//         Route::post('save/{id?}', [$Controller, 'Save'])->name('save');
//         Route::match(['get', 'post'], 'status/{id}/{status}', [$Controller, 'updateStatus'])->name('status');
//         Route::post('delete/{id?}', [$Controller, 'delete'])->name('delete');
//         Route::post('restore/{id?}', [$Controller, 'restore'])->name('restore');
//         Route::post('destroy/{id?}', [$Controller, 'destroy'])->name('destroy');

//         Route::get('change-password/{id?}', [$Controller, 'onChangePassword'])->name('change-password');
//         Route::post('save-password/{id?}', [$Controller, 'onSavePassword'])->name('save-password');

//         Route::get('permission/{id?}', [$Controller, 'onPermission'])->name('permission');
//         Route::post('save-permission/{id?}', [$Controller, 'onSavePermission'])->name('save-permission');
//     });
// }


Route::prefix('auth')->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin-login');
    });
    Route::get('/', [UserController::class, 'login'])->name('login');
    Route::get('/forgot', [UserController::class, 'forgotPassword'])->name('forgot-password');
    Route::post('/login/post', [AuthController::class, 'login'])->name('login-post');
    Route::get('/sign-out', [AuthController::class, 'signOut'])->name('sign-out');
});

Route::middleware(['AdminGuard'])
    ->group(function () {
        Route::get('/', function () {
            return redirect()->route('admin-dashboard');
        });

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        //Service
        Route::group([
            'prefix' => 'service',
            'as'     => 'service-'
        ], function () {
            Route::get('list/{status?}', [ServiceController::class, 'index'])->name('list');
            Route::get('create/{id?}', [ServiceController::class, 'onCreate'])->name('create');
            Route::post('save/{id?}', [ServiceController::class, 'onSave'])->name('save');
            Route::match(['get', 'post'], 'status/{id}/{status}', [ServiceController::class, 'onUpdateStatus'])->name('status');
        });

        // setting
        // Category
        Route::group([
            'prefix' => 'category',
            'as'     => 'category-'
        ], function () {
            Route::get('list/{status?}', [CategoryController::class, 'index'])->name('list');
            Route::get('data', [CategoryController::class, 'data'])->name('data');
            Route::get('create', [CategoryController::class, 'onCreate'])->name('create');
            Route::get('edit/{id?}', [CategoryController::class, 'onEdit'])->name('edit');
            Route::post('save/{id?}', [CategoryController::class, 'onSave'])->name('save');
            Route::match(['get', 'post'], 'status/{id}/{status}', [CategoryController::class, 'onUpdateStatus'])->name('status');
        });

        CRUD(PartnerController::class, 'partner');
        CRUD(SectorController::class, 'sector');
        CRUD(PositionController::class, 'position');
        CRUD(UserController::class, 'user');

        CRUD(JobController::class, 'internships');
        CRUD(BlogController::class, 'blog');

        CRUD(PlacementTypeController::class, 'placement-type');

        CRUD(TestimonialController::class, 'testimonial');

        CRUD(CustomerController::class, 'customer');
        Route::get('customer/export', [CustomerController::class, 'export'])->name('customer-export');
        CRUD(CustomerPaidController::class, 'customer-paid');
        Route::get('customer-paid/export', [CustomerPaidController::class, 'export'])->name('customer-paid-export');

        Route::get('/OurService', [SettingController::class, 'indexOurService'])->name('our-service-index');
        Route::post('/OurService/Save', [SettingController::class, 'onStoreOurService'])->name('our-service-store');

        Route::get('/aboutUs', [SettingController::class, 'indexAboutUs'])->name('about-us-index');
        Route::post('/aboutUs/Save', [SettingController::class, 'onStoreAboutUs'])->name('about-us-store');

        //Select
        Route::group([
            'prefix' => 'select',
            'as'     => 'select-'
        ], function () {

            Route::get('product', [Admin\SelectController::class, 'selectProduct'])->name('product');
            Route::get('customer', [Admin\SelectController::class, 'selectCustomer'])->name('customer');
            Route::get('supplier', [Admin\SelectController::class, 'selectSupplier'])->name('supplier');

            Route::get('stock-product', [Admin\SelectController::class, 'stockSelectProduct'])->name('stock-product');
            Route::get('stock-shop', [Admin\SelectController::class, 'stockSelectShop'])->name('stock-shop');
            Route::get('shopNotIn', [Admin\SelectController::class, 'stockSelectShopNotInID'])->name('shopNotIn');

            //shopProduct
            Route::get('shop-product', [Admin\SelectController::class, 'selectShopProduct'])->name('shop-product');

            //findProductStock
            Route::get('find-shop-product', [Admin\SelectController::class, 'findShopProduct'])->name('find-shop-product');

            //selectBarber
            Route::get('barber', [Admin\SelectController::class, 'SelectBarber'])->name('barber');

            //selectShop
            Route::get('shop', [Admin\SelectController::class, 'SelectShop'])->name('shop');

            //product
            Route::get('type-product', [Admin\SelectController::class, 'SelectProductSearch'])->name('product');
            //service
            Route::get('type-service', [Admin\SelectController::class, 'SelectServiceSearch'])->name('service');

            //In
            Route::get('shop-in-product', [Admin\SelectController::class, 'productInShop'])->name('shop-in-product');

            //Position
            Route::get('position', [Admin\SelectController::class, 'SelectPositionSearch'])->name('position');
            //Sector
            Route::get('sector', [Admin\SelectController::class, 'SelectSectorSearch'])->name('sector');

            //placement type
            Route::get('placement-type', [Admin\SelectController::class, 'SelectPlacementTypeSearch'])->name('placement-type');
        });


        //Setting
        Route::group([
            'prefix' => 'setting',
            'as' => 'setting-',
        ], function () {
            Route::get('/data', [SettingController::class, 'index'])->name('setting');
            Route::post('save/{id?}', [SettingController::class, 'store'])->name('save');
        });

        //File Manager
        Route::prefix('file-manager')
            ->name('file-manager-')
            ->group(function () {
                Route::get('/index', [FileManager::class, 'index'])->name('index');
                Route::get('/first', [FileManager::class, 'first'])->name('first');
                Route::get('/files', [FileManager::class, 'getFiles'])->name('files');
                Route::get('/folders', [FileManager::class, 'getFolders'])->name('folders');
                Route::post('/upload', [FileManager::class, 'uploadFile'])->name('upload');
                Route::post('/rename-file', [FileManager::class, 'renameFile'])->name('rename-file');
                Route::delete('/delete-file', [FileManager::class, 'deleteFile'])->name('delete-file');

                //folder
                Route::post('/create-folder', [FileManager::class, 'createFolder'])->name('create-folder');
                Route::post('/rename-folder', [FileManager::class, 'renameFolder'])->name('rename-folder');
                Route::delete('/delete-folder', [FileManager::class, 'deleteFolder'])->name('delete-folder');

                //trash bin
                Route::delete('/delete-all', [FileManager::class, 'deleteAll'])->name('delete-all');
                Route::put('/restore-all', [FileManager::class, 'restoreAll'])->name('restore-all');
            });

        // Page
        Route::group([
            'prefix' => 'page',
            'as' => 'page-',
        ], function () {
            Route::get('/{type?}', [Admin\PageController::class, 'page'])->name('page');
            Route::post('save/{id?}', [Admin\PageController::class, 'onSave'])->name('save');
        });

        //Contact
        Route::group([
            'prefix' => 'contact',
            'as' => 'contact-',
        ], function () {
            Route::get('/{type?}', [Admin\ContactController::class, 'index'])->name('contact');
            Route::post('save/{id?}', [Admin\ContactController::class, 'store'])->name('save');
        });
    });


Route::get('clear-cache-s', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    return "Cache is cleared";
});
Route::get('clear-cache', function () {
    Artisan::call('optimize:clear');
    return "Cache is cleared";
});
