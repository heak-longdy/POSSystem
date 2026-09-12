<?php

use App\Services\FileManager;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Admin as Admin;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DashboardController;

use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ShopController;

use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\RemainingAmountController;

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
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\SectorController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\SupplierController;

use App\Http\Controllers\Admin\UomController;

use App\Http\Controllers\Admin\Inventory\StockInController;
use App\Http\Controllers\Admin\Inventory\StockOutController;
use App\Http\Controllers\Admin\Inventory\StockTransferController;
use App\Http\Controllers\Admin\Inventory\StockOnHandController;
use App\Http\Controllers\Admin\Inventory\StockMovementController;
use App\Http\Controllers\Admin\Report\SalesReportController;
use App\Http\Controllers\Admin\Report\OrderTransactionReportController;
use App\Http\Controllers\Admin\Report\InventoryMovementReportController;
use App\Http\Controllers\Admin\Report\StaffExpenseReportController;

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
        // Route::group([
        //     'prefix' => 'service',
        //     'as'     => 'service-'
        // ], function () {
        //     Route::get('list/{status?}', [ServiceController::class, 'index'])->name('list');
        //     Route::get('create/{id?}', [ServiceController::class, 'onCreate'])->name('create');
        //     Route::post('save/{id?}', [ServiceController::class, 'onSave'])->name('save');
        //     Route::match(['get', 'post'], 'status/{id}/{status}', [ServiceController::class, 'onUpdateStatus'])->name('status');
        // });


        CRUD(ProductController::class, 'product');
        CRUD(ShopController::class, 'shop');
        Route::group([
            'prefix' => 'shop',
            'as'     => 'shop-'
        ], function () {
            Route::get('product/{id}/create', [ShopController::class, 'onCreateProduct'])->name('product-create');
            Route::get('product/{shopId}/edit/{shopProductId}', [ShopController::class, 'onEditProduct'])->name('product-edit');
            Route::post('product/{shopId}/delete/{shopProductId}', [ShopController::class, 'deleteProduct'])->name('product-delete');
            Route::post('product/{shopId}/restore/{shopProductId}', [ShopController::class, 'restoreProduct'])->name('product-restore');
            Route::post('product/{shopId}/destroy/{shopProductId}', [ShopController::class, 'destroyProduct'])->name('product-destroy');
            Route::get('product/{id}', [ShopController::class, 'onProduct'])->name('product');
            Route::post('product/save/{id}', [ShopController::class, 'saveProduct'])->name('product-save');
        });

        // setting
        CRUD(CategoryController::class, 'category');

        CRUD(SupplierController::class, 'supplier');

        CRUD(PartnerController::class, 'partner');
        CRUD(SectorController::class, 'sector');
        CRUD(PositionController::class, 'position');
        CRUD(StaffController::class, 'staff');
        CRUD(UserController::class, 'user');

        CRUD(JobController::class, 'internships');
        CRUD(BlogController::class, 'blog');

        CRUD(PlacementTypeController::class, 'placement-type');

        CRUD(TestimonialController::class, 'testimonial');

        CRUD(CustomerController::class, 'customer');

        CRUD(CustomerPaidController::class, 'customer-paid');
        Route::get('customer-paid/export', [CustomerPaidController::class, 'export'])->name('customer-paid-export');

        Route::get('/OurService', [SettingController::class, 'indexOurService'])->name('our-service-index');
        Route::post('/OurService/Save', [SettingController::class, 'onStoreOurService'])->name('our-service-store');

        Route::get('/aboutUs', [SettingController::class, 'indexAboutUs'])->name('about-us-index');
        Route::post('/aboutUs/Save', [SettingController::class, 'onStoreAboutUs'])->name('about-us-store');



         //InventoryManagement
        //stockIn
        Route::group([
            'prefix' => 'stock-in',
            'as'     => 'stock-in-'
        ], function () {
            Route::get('list/{status?}', [StockInController::class, 'index'])->name('list');
            Route::get('create', [StockInController::class, 'onCreate'])->name('create');
            Route::get('edit/{id?}', [StockInController::class, 'onEdit'])->name('edit');
            Route::get('view/{id?}', [StockInController::class, 'onView'])->name('view');
            Route::post('save/{id?}', [StockInController::class, 'onSave'])->name('save');
            Route::match(['get', 'post'], 'status/{id}/{status}', [StockInController::class, 'updateStatus'])->name('status');
            Route::post('delete/{id?}', [StockInController::class, 'delete'])->name('delete');
            Route::post('restore/{id?}', [StockInController::class, 'restore'])->name('restore');
            Route::post('destroy/{id?}', [StockInController::class, 'destroy'])->name('destroy');
        });
        //stockOut
        Route::group([
            'prefix' => 'stock-out',
            'as'     => 'stock-out-'
        ], function () {
            Route::get('list/{status?}', [StockOutController::class, 'index'])->name('list');
            Route::get('create', [StockOutController::class, 'onCreate'])->name('create');
            Route::get('edit/{id?}', [StockOutController::class, 'onEdit'])->name('edit');
            Route::get('view/{id?}', [StockOutController::class, 'onView'])->name('view');
            Route::post('save/{id?}', [StockOutController::class, 'onSave'])->name('save');
            Route::match(['get', 'post'], 'status/{id}/{status}', [StockOutController::class, 'updateStatus'])->name('status');
            Route::post('delete/{id?}', [StockOutController::class, 'delete'])->name('delete');
            Route::post('restore/{id?}', [StockOutController::class, 'restore'])->name('restore');
            Route::post('destroy/{id?}', [StockOutController::class, 'destroy'])->name('destroy');
        });
        //stockTransfer
        Route::group([
            'prefix' => 'stock-transfer',
            'as'     => 'stock-transfer-'
        ], function () {
            Route::get('list/{status?}', [StockTransferController::class, 'index'])->name('list');
            Route::get('create', [StockTransferController::class, 'onCreate'])->name('create');
            Route::get('edit/{id?}', [StockTransferController::class, 'onEdit'])->name('edit');
            Route::get('view/{id?}', [StockTransferController::class, 'onView'])->name('view');
            Route::post('save/{id?}', [StockTransferController::class, 'onSave'])->name('save');
            Route::post('update/{id?}', [StockTransferController::class, 'onUpdate'])->name('update');
            Route::match(['get', 'post'], 'status/{id}/{status}', [StockTransferController::class, 'updateStatus'])->name('status');
            Route::post('delete/{id?}', [StockTransferController::class, 'delete'])->name('delete');
            Route::post('restore/{id?}', [StockTransferController::class, 'restore'])->name('restore');
            Route::post('destroy/{id?}', [StockTransferController::class, 'destroy'])->name('destroy');
        });
        //stockOnHand
        Route::group([
            'prefix' => 'stock-on-hand',
            'as'     => 'stock-on-hand-'
        ], function () {
            Route::get('list/{status?}', [StockOnHandController::class, 'index'])->name('list');
            Route::get('view/{id?}', [StockOnHandController::class, 'onView'])->name('view');
            Route::get('find/{product_id?}/{shop_id?}', [StockOnHandController::class, 'onFind'])->name('onFind');
            Route::get('report', [StockOnHandController::class, 'report'])->name('report');
        });
        //stockMovement
        Route::group([
            'prefix' => 'stock-movement',
            'as'     => 'stock-movement-'
        ], function () {
            Route::get('list/{status?}', [StockMovementController::class, 'index'])->name('list');
            Route::get('view/{id?}', [StockMovementController::class, 'onView'])->name('view');
            Route::get('report', [StockMovementController::class, 'report'])->name('report');
        });

        //Setting
        CRUD(UomController::class, 'uom');

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

            //Category
            Route::get('category', [Admin\SelectController::class, 'SelectCategorySearch'])->name('category');

            //UOM
            Route::get('uom', [Admin\SelectController::class, 'SelectUOMSearch'])->name('uom');

            //placement type
            Route::get('placement-type', [Admin\SelectController::class, 'SelectPlacementTypeSearch'])->name('placement-type');
        });


        //Bookings
        Route::group([
            'prefix' => 'booking',
            'as'     => 'booking-'
        ], function () {
            Route::get('list/{status?}', [BookingController::class, 'index'])->name('list');
            Route::get('create', [BookingController::class, 'onCreate'])->name('create');
            Route::match(['get', 'post'], 'status/{id}/{status}', [BookingController::class, 'updateStatus'])->name('status');
            Route::get('list-product/{status?}', [BookingController::class, 'product'])->name('list-product');
            Route::get('edit/{id?}', [BookingController::class, 'onEdit'])->name('edit');
            Route::get('detail/{id}', [BookingController::class, 'show'])->name('detail');
            Route::post('save/{id?}', [BookingController::class, 'Save'])->name('save');
            Route::post('delete/{id}', [BookingController::class, 'delete'])->name('delete');
            Route::post('restore/{id}', [BookingController::class, 'restore'])->name('restore');
            Route::post('destroy/{id}', [BookingController::class, 'destroy'])->name('destroy');

            Route::get('report', [BookingController::class, 'report'])->name('report');
            Route::get('payment-details/{id}', [BookingController::class, 'getPaymentDetails'])->name('payment-details');
            Route::post('add-payment/{id}', [BookingController::class, 'addPayment'])->name('add-payment');
            Route::put('update-payment/{paymentId}', [BookingController::class, 'updatePayment'])->name('update-payment');
            Route::delete('delete-payment/{paymentId}', [BookingController::class, 'deletePayment'])->name('delete-payment');
            Route::post('send-payment-reminder/{id}', [BookingController::class, 'sendPaymentReminder'])->name('send-payment-reminder');
            Route::post('cancel/{id}', [BookingController::class, 'cancelBooking'])->name('cancel');
            Route::post('update-payment-status/{id}', [BookingController::class, 'updatePaymentStatus'])->name('update-payment-status');
        });

        // Remaining Amount Management
        Route::group([
            'prefix' => 'remaining-amount',
            'as'     => 'remaining-amount-'
        ], function () {
            Route::get('list/{status?}', [RemainingAmountController::class, 'index'])->name('list');
            Route::get('create', [RemainingAmountController::class, 'index'])->name('create');
            Route::get('payment-details/{id}', [RemainingAmountController::class, 'getPaymentDetails'])->name('payment-details');
            Route::post('add-payment/{id}', [RemainingAmountController::class, 'addPayment'])->name('add-payment');
            Route::put('update-payment/{paymentId}', [RemainingAmountController::class, 'updatePayment'])->name('update-payment');
            Route::delete('delete-payment/{paymentId}', [RemainingAmountController::class, 'deletePayment'])->name('delete-payment');
            Route::post('send-reminder/{id}', [RemainingAmountController::class, 'sendPaymentReminder'])->name('send-reminder');
            Route::get('report', [RemainingAmountController::class, 'report'])->name('report');
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

        // Contact
        Route::group([
            'prefix' => 'contact',
            'as' => 'contact-',
        ], function () {
            Route::get('/{type?}', [Admin\ContactController::class, 'index'])->name('contact');
            Route::post('save/{id?}', [Admin\ContactController::class, 'store'])->name('save');
        });

        // Staff Expense Management
        CRUD(Admin\StaffExpenseController::class, 'staff-expense');
        Route::group([
            'prefix' => 'staff-expense',
            'as'     => 'staff-expense-',
        ], function () {
            Route::get('/staff/{staffId}',  [Admin\StaffExpenseController::class, 'staffHistory'])->name('staff-history');
        });

        // Report Management - Sales Report
        Route::group([
            'prefix' => 'report/sales',
            'as'     => 'report-sales-',
        ], function () {
            Route::get('/', [SalesReportController::class, 'index'])->name('index');
            Route::get('daily', [SalesReportController::class, 'daily'])->name('daily');
            Route::get('monthly', [SalesReportController::class, 'monthly'])->name('monthly');
            Route::get('report', [SalesReportController::class, 'report'])->name('report');
            Route::get('details/{period}', [SalesReportController::class, 'details'])->name('details');
        });

        // Report Management - Order Transaction Report
        Route::group([
            'prefix' => 'report/order-transaction',
            'as'     => 'report-order-transaction-',
        ], function () {
            Route::get('/', [OrderTransactionReportController::class, 'index'])->name('index');
            Route::get('daily', [OrderTransactionReportController::class, 'daily'])->name('daily');
            Route::get('monthly', [OrderTransactionReportController::class, 'monthly'])->name('monthly');
            Route::get('report', [OrderTransactionReportController::class, 'report'])->name('report');
            Route::get('details/{period}', [OrderTransactionReportController::class, 'details'])->name('details');
        });

        // Report Management - Inventory Movement Report
        Route::group([
            'prefix' => 'report/inventory-movement',
            'as'     => 'report-inventory-movement-',
        ], function () {
            Route::get('/', [InventoryMovementReportController::class, 'index'])->name('index');
            Route::get('daily', [InventoryMovementReportController::class, 'daily'])->name('daily');
            Route::get('monthly', [InventoryMovementReportController::class, 'monthly'])->name('monthly');
            Route::get('report', [InventoryMovementReportController::class, 'report'])->name('report');
            Route::get('details/{period}', [InventoryMovementReportController::class, 'details'])->name('details');
        });

        // Report Management - Staff Expense Report
        Route::group([
            'prefix' => 'report/staff-expense',
            'as'     => 'report-staff-expense-',
        ], function () {
            Route::get('/', [StaffExpenseReportController::class, 'index'])->name('index');
            Route::get('daily', [StaffExpenseReportController::class, 'daily'])->name('daily');
            Route::get('monthly', [StaffExpenseReportController::class, 'monthly'])->name('monthly');
            Route::get('report', [StaffExpenseReportController::class, 'report'])->name('report');
            Route::get('details/{period}', [StaffExpenseReportController::class, 'details'])->name('details');
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
