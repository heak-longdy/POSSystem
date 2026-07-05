<?php

namespace Database\Seeders;

use App\Models\ModulePermission;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public $index = 0;
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        ModulePermission::truncate();
        Permission::truncate();
        Schema::enableForeignKeyConstraints();
        $view = "View";
        $create = "Create";
        $edit = "Edit";
        $delete = "Delete";
        $trash = "Trash";
        $destroy = "Destroy";
        $excel = "Excel";
        $reportExcel = "Report Excel";

        $stDashboard =  $this->increaseIndex();
        $stBooking =  $this->increaseIndex();
        $stCustomer =  $this->increaseIndex();
        $stShop =  $this->increaseIndex();
        $stBarber =  $this->increaseIndex();
        $stWallet =  $this->increaseIndex();
        $stProduct =  $this->increaseIndex();
        $stService =  $this->increaseIndex();
        $stPromotion =  $this->increaseIndex();
        $stSlide =  $this->increaseIndex();
        $stUser =  $this->increaseIndex();
        $stPage =  $this->increaseIndex();
        $stSetting =  $this->increaseIndex();
       
        $stCustomerPoint = $this->increaseIndex();
        $stReportTransaction = $this->increaseIndex();
        $stReportSummary = $this->increaseIndex();

        $stStockIn = $this->increaseIndex();
        $stStockOut = $this->increaseIndex();
        $stStockTransfer = $this->increaseIndex();
        $stStockOnHand = $this->increaseIndex();
        $stStockMovement = $this->increaseIndex();

        $stAbout =  $this->increaseIndex();
        $stPolicy =  $this->increaseIndex();
        $stContact =  $this->increaseIndex();

        $stCategory =  $this->increaseIndex();
        $stSupplier = $this->increaseIndex();
        $stUom =  $this->increaseIndex();
        $stBrand = $this->increaseIndex();
        $stBrandSetting = $this->increaseIndex();
        $stPoint =  $this->increaseIndex();
        $stTopUpRate = $this->increaseIndex();

        $gpInventoryManagement = $this->increaseIndex();
        $gpPage = $this->increaseIndex();
        $gpSetting = $this->increaseIndex();

        
       

        //dashboard
        $dashboard = ModulePermission::create([
            'name' => 'Dashboard',
            'parent_id' => $stDashboard,
            'sort_no' => $stDashboard,
        ]);
        Permission::create([
            'display_name' => $view,
            'name' => 'dashboard-view',
            'guard_name' => 'web',
            'module_id' => $dashboard->id,
        ]);

        //Booking
        $booking = ModulePermission::create([
            'name' => 'Booking',
            'parent_id' => $stBooking,
            'sort_no' => $stBooking,

        ]);
        Permission::insert([
            [
                'display_name' => $view,
                'name' => 'booking-view',
                'guard_name' => 'web',
                'module_id' => $booking->id,
            ],
            [
                'display_name' => $create,
                'name' => 'booking-create',
                'guard_name' => 'web',
                'module_id' => $booking->id,
            ],
            [
                'display_name' => $edit,
                'name' => 'booking-update',
                'guard_name' => 'web',
                'module_id' => $booking->id,
            ],
            [
                'display_name' => $delete,
                'name' => 'booking-delete',
                'guard_name' => 'web',
                'module_id' => $booking->id,
            ],
        ]);
        
        //end Booking

        //Customer
        $customer = ModulePermission::create([
            'name' => 'Customer',
            'parent_id' => $stCustomer,
            'sort_no' => $stCustomer,

        ]);
        Permission::insert([
            [
                'display_name' => $view,
                'name' => 'customer-view',
                'guard_name' => 'web',
                'module_id' => $customer->id,
            ],
            [
                'display_name' => $create,
                'name' => 'customer-create',
                'guard_name' => 'web',
                'module_id' => $customer->id,
            ],
            [
                'display_name' => $edit,
                'name' => 'customer-update',
                'guard_name' => 'web',
                'module_id' => $customer->id,
            ],
        ]);
        //endCustomer

        //Shop
        $shop = ModulePermission::create([
            'name' => 'Shop',
            'parent_id' => $stShop,
            'sort_no' => $stShop,

        ]);
        Permission::insert([
            [
                'display_name' => $view,
                'name' => 'shop-view',
                'guard_name' => 'web',
                'module_id' => $shop->id,
            ],
            [
                'display_name' => $create,
                'name' => 'shop-create',
                'guard_name' => 'web',
                'module_id' => $shop->id,
            ],
            [
                'display_name' => $edit,
                'name' => 'shop-update',
                'guard_name' => 'web',
                'module_id' => $shop->id,
            ],
        ]);
        //endShop

        //Barber
        $barber = ModulePermission::create([
            'name' => 'Barber',
            'parent_id' => $stBarber,
            'sort_no' => $stBarber,

        ]);
        Permission::insert([
            [
                'display_name' => $view,
                'name' => 'barber-view',
                'guard_name' => 'web',
                'module_id' => $barber->id,
            ],
            [
                'display_name' => $create,
                'name' => 'barber-create',
                'guard_name' => 'web',
                'module_id' => $barber->id,
            ],
            [
                'display_name' => $edit,
                'name' => 'barber-update',
                'guard_name' => 'web',
                'module_id' => $barber->id,
            ],
        ]);
        //endBarber

        //Wallet
        $wallet = ModulePermission::create([
            'name' => 'Wallet',
            'parent_id' => $stWallet,
            'sort_no' => $stWallet,

        ]);
        Permission::insert([
            [
                'display_name' => $view,
                'name' => 'wallet-view',
                'guard_name' => 'web',
                'module_id' => $wallet->id,
            ],
            [
                'display_name' => $create,
                'name' => 'wallet-create',
                'guard_name' => 'web',
                'module_id' => $wallet->id,
            ],
            [
                'display_name' => $edit,
                'name' => 'wallet-update',
                'guard_name' => 'web',
                'module_id' => $wallet->id,
            ],
        ]);
        //endWallet

        //Product
        $product = ModulePermission::create([
            'name' => 'Product',
            'parent_id' => $stProduct,
            'sort_no' => $stProduct,

        ]);
        Permission::insert([
            [
                'display_name' => $view,
                'name' => 'product-view',
                'guard_name' => 'web',
                'module_id' => $product->id,
            ],
            [
                'display_name' => $create,
                'name' => 'product-create',
                'guard_name' => 'web',
                'module_id' => $product->id,
            ],
            [
                'display_name' => $edit,
                'name' => 'product-update',
                'guard_name' => 'web',
                'module_id' => $product->id,
            ],
        ]);
        //endProduct

        //Service
        $service = ModulePermission::create([
            'name' => 'Service',
            'parent_id' => $stService,
            'sort_no' => $stService,

        ]);
        Permission::insert([
            [
                'display_name' => $view,
                'name' => 'service-view',
                'guard_name' => 'web',
                'module_id' => $service->id,
            ],
            [
                'display_name' => $create,
                'name' => 'service-create',
                'guard_name' => 'web',
                'module_id' => $service->id,
            ],
            [
                'display_name' => $edit,
                'name' => 'service-update',
                'guard_name' => 'web',
                'module_id' => $service->id,
            ],
        ]);
        //endService

        //Promotion
        $promotion = ModulePermission::create([
            'name' => 'Promotion',
            'parent_id' => $stPromotion,
            'sort_no' => $stPromotion,

        ]);
        Permission::insert([
            [
                'display_name' => $view,
                'name' => 'promotion-view',
                'guard_name' => 'web',
                'module_id' => $promotion->id,
            ],
            [
                'display_name' => $create,
                'name' => 'promotion-create',
                'guard_name' => 'web',
                'module_id' => $promotion->id,
            ],
            [
                'display_name' => $edit,
                'name' => 'promotion-update',
                'guard_name' => 'web',
                'module_id' => $promotion->id,
            ],
            [
                'display_name' => $delete,
                'name' => 'promotion-delete',
                'guard_name' => 'web',
                'module_id' => $promotion->id,
            ],
        ]);
        //endPromotion

        //Banner
        $slide = ModulePermission::create([
            'name' => 'Banner',
            'parent_id' => $stSlide,
            'sort_no' => $stSlide,

        ]);
        Permission::insert([
            [
                'display_name' => $view,
                'name' => 'slide-view',
                'guard_name' => 'web',
                'module_id' => $slide->id,
            ],
            [
                'display_name' => $create,
                'name' => 'slide-create',
                'guard_name' => 'web',
                'module_id' => $slide->id,
            ],
            [
                'display_name' => $edit,
                'name' => 'slide-update',
                'guard_name' => 'web',
                'module_id' => $slide->id,
            ],
            [
                'display_name' => $delete,
                'name' => 'slide-delete',
                'guard_name' => 'web',
                'module_id' => $slide->id,
            ],
        ]);
        //endBanner

        //User
        $user = ModulePermission::create([
            'name' => 'User',
            'parent_id' => $stUser,
            'sort_no' => $stUser,

        ]);
        Permission::insert([
            [
                'display_name' => $view,
                'name' => 'user-view',
                'guard_name' => 'web',
                'module_id' => $user->id,
            ],
            [
                'display_name' => $create,
                'name' => 'user-create',
                'guard_name' => 'web',
                'module_id' => $user->id,
            ],
            [
                'display_name' => $edit,
                'name' => 'user-update',
                'guard_name' => 'web',
                'module_id' => $user->id,
            ],
            [
                'display_name' => $delete,
                'name' => 'user-delete',
                'guard_name' => 'web',
                'module_id' => $user->id,
            ],
        ]);
        //endUser

        //customerPoint
        $customerPoint = ModulePermission::create([
            'name' => 'Customer Point',
            'parent_id' => $stCustomerPoint,
            'sort_no' => $stCustomerPoint,

        ]);
        Permission::insert([
            [
                'display_name' => $view,
                'name' => 'customer-point-view',
                'guard_name' => 'web',
                'module_id' => $customerPoint->id,
            ],
            [
                'display_name' => $excel,
                'name' => 'customer-point-excel',
                'guard_name' => 'web',
                'module_id' => $customerPoint->id,
            ]
        ]);
        //endCustomerPoint
        //reportTransaction
        $reportTransaction = ModulePermission::create([
            'name' => 'Report Transaction',
            'parent_id' => $stReportTransaction,
            'sort_no' => $stReportTransaction,

        ]);
        Permission::insert([
            [
                'display_name' => $view,
                'name' => 'report-transaction-view',
                'guard_name' => 'web',
                'module_id' => $reportTransaction->id,
            ],
            [
                'display_name' => $excel,
                'name' => 'report-transaction-excel',
                'guard_name' => 'web',
                'module_id' => $reportTransaction->id,
            ]
        ]);
        //endReportTransaction
        //reportSummary
        $reportSummary = ModulePermission::create([
            'name' => 'Report Summary',
            'parent_id' => $stReportSummary,
            'sort_no' => $stReportSummary,

        ]);
        Permission::insert([
            [
                'display_name' => $view,
                'name' => 'report-summary-view',
                'guard_name' => 'web',
                'module_id' => $reportSummary->id,
            ]
        ]);
        //endReportTransaction


        // InventoryManagement
        // StockIn
        $stockIn = ModulePermission::create([
            'name' => 'Stock In',
            'parent_id' => $gpInventoryManagement,
            'parent_name' => 'Inventory Management',
            'sort_no' => $stStockIn,
        ]);
        Permission::insert([
            [
                'display_name' => $view,
                'name' => 'stock-in-view',
                'guard_name' => 'web',
                'module_id' => $stockIn->id,
            ],
            [
                'display_name' => $create,
                'name' => 'stock-in-create',
                'guard_name' => 'web',
                'module_id' => $stockIn->id,
            ]
        ]);
        // StockOut
        $stockOut = ModulePermission::create([
            'name' => 'Stock Out',
            'parent_id' => $gpInventoryManagement,
            'parent_name' => 'Inventory Management',
            'sort_no' => $stStockOut,
        ]);
        Permission::insert([
            [
                'display_name' => $view,
                'name' => 'stock-out-view',
                'guard_name' => 'web',
                'module_id' => $stockOut->id,
            ],
            [
                'display_name' => $create,
                'name' => 'stock-out-create',
                'guard_name' => 'web',
                'module_id' => $stockOut->id,
            ]
        ]);
        // StockTransfer
        $stockTransfer = ModulePermission::create([
            'name' => 'Stock Transfer',
            'parent_id' => $gpInventoryManagement,
            'parent_name' => 'Inventory Management',
            'sort_no' => $stStockTransfer,
        ]);
        Permission::insert([
            [
                'display_name' => $view,
                'name' => 'stock-transfer-view',
                'guard_name' => 'web',
                'module_id' => $stockTransfer->id,
            ],
            [
                'display_name' => $create,
                'name' => 'stock-transfer-create',
                'guard_name' => 'web',
                'module_id' => $stockTransfer->id,
            ]
        ]);
        // StockOnHand
        $stockOnHand = ModulePermission::create([
            'name' => 'Stock On Hand',
            'parent_id' => $gpInventoryManagement,
            'parent_name' => 'Inventory Management',
            'sort_no' => $stStockOnHand,
        ]);
        Permission::insert([
            [
                'display_name' => $view,
                'name' => 'stock-on-hand-view',
                'guard_name' => 'web',
                'module_id' => $stockOnHand->id,
            ],
            [
                'display_name' => $reportExcel,
                'name' => 'stock-on-hand-excel',
                'guard_name' => 'web',
                'module_id' => $stockOnHand->id,
            ]
        ]);
        // StockMovement
        $stockMovement = ModulePermission::create([
            'name' => 'Stock Movement',
            'parent_id' => $gpInventoryManagement,
            'parent_name' => 'Inventory Management',
            'sort_no' => $stStockMovement,
        ]);
        Permission::insert([
            [
                'display_name' => $view,
                'name' => 'stock-movement-view',
                'guard_name' => 'web',
                'module_id' => $stockMovement->id,
            ],
            [
                'display_name' => $reportExcel,
                'name' => 'stock-movement-excel',
                'guard_name' => 'web',
                'module_id' => $stockMovement->id,
            ]
        ]);

        // Page
        //About us
        $aboutUs = ModulePermission::create([
            'name' => 'About us',
            'parent_id' => $gpPage,
            'parent_name' => 'Page Management',
            'sort_no' => $stAbout,

        ]);
        Permission::insert([
            [
                'display_name' => $view,
                'name' => 'about-view',
                'guard_name' => 'web',
                'module_id' => $aboutUs->id,
            ],
            [
                'display_name' => $edit,
                'name' => 'about-update',
                'guard_name' => 'web',
                'module_id' => $aboutUs->id,
            ],
        ]);
        //endAboutUs
        
        //Privacy & Policy
        $policy = ModulePermission::create([
            'name' => 'Privacy & Policy',
            'parent_id' => $gpPage,
            'parent_name' => 'Page Management',
            'sort_no' => $stPolicy,

        ]);
        Permission::insert([
            [
                'display_name' => $view,
                'name' => 'privacy-view',
                'guard_name' => 'web',
                'module_id' => $policy->id,
            ],
            [
                'display_name' => $edit,
                'name' => 'privacy-update',
                'guard_name' => 'web',
                'module_id' => $policy->id,
            ],
        ]);
        //endPrivacy & Policy
        //Contact Us
        $contactUs = ModulePermission::create([
            'name' => 'Contact us',
            'parent_id' => $gpPage,
            'parent_name' => 'Page Management',
            'sort_no' => $stContact,

        ]);
        Permission::insert([
            [
                'display_name' => $view,
                'name' => 'contact-view',
                'guard_name' => 'web',
                'module_id' => $contactUs->id,
            ],
            [
                'display_name' => $edit,
                'name' => 'contact-update',
                'guard_name' => 'web',
                'module_id' => $contactUs->id,
            ],
        ]);
        //endContact
        // Setting
        //Category
        $category = ModulePermission::create([
            'name' => 'Category',
            'parent_id' => $gpSetting,
            'parent_name' => 'Setting',
            'sort_no' => $stCategory,

        ]);
        Permission::insert([
            [
                'display_name' => $view,
                'name' => 'category-view',
                'guard_name' => 'web',
                'module_id' => $category->id,
            ],
            [
                'display_name' => $create,
                'name' => 'category-create',
                'guard_name' => 'web',
                'module_id' => $category->id,
            ],
            [
                'display_name' => $edit,
                'name' => 'category-update',
                'guard_name' => 'web',
                'module_id' => $category->id,
            ],
        ]);
        //endCategory
        //Supplier
        $supplier = ModulePermission::create([
            'name' => 'Supplier',
            'parent_id' => $gpSetting,
            'parent_name' => 'Setting',
            'sort_no' => $stSupplier,

        ]);
        Permission::insert([
            [
                'display_name' => $view,
                'name' => 'supplier-view',
                'guard_name' => 'web',
                'module_id' => $supplier->id,
            ],
            [
                'display_name' => $create,
                'name' => 'supplier-create',
                'guard_name' => 'web',
                'module_id' => $supplier->id,
            ],
            [
                'display_name' => $edit,
                'name' => 'supplier-update',
                'guard_name' => 'web',
                'module_id' => $supplier->id,
            ],
        ]);
        //endCategory
        //Uom
        $uom = ModulePermission::create([
            'name' => 'UOM',
            'parent_id' => $gpSetting,
            'parent_name' => 'Setting',
            'sort_no' => $stUom,

        ]);
        Permission::insert([
            [
                'display_name' => $view,
                'name' => 'uom-view',
                'guard_name' => 'web',
                'module_id' => $uom->id,
            ],
            [
                'display_name' => $create,
                'name' => 'uom-create',
                'guard_name' => 'web',
                'module_id' => $uom->id,
            ],
            [
                'display_name' => $edit,
                'name' => 'uom-update',
                'guard_name' => 'web',
                'module_id' => $uom->id,
            ],
        ]);
        //endUOM
        //Brand
        $brand = ModulePermission::create([
            'name' => 'Brand',
            'parent_id' => $gpSetting,
            'parent_name' => 'Setting',
            'sort_no' => $stBrand,

        ]);
        Permission::insert([
            [
                'display_name' => $view,
                'name' => 'brand-view',
                'guard_name' => 'web',
                'module_id' => $brand->id,
            ],
            [
                'display_name' => $create,
                'name' => 'brand-create',
                'guard_name' => 'web',
                'module_id' => $brand->id,
            ],
            [
                'display_name' => $edit,
                'name' => 'brand-update',
                'guard_name' => 'web',
                'module_id' => $brand->id,
            ],
        ]);
        //endBrand
        //brandSetting
        $brandSetting = ModulePermission::create([
            'name' => 'Brand Setting',
            'parent_id' => $gpSetting,
            'parent_name' => 'Setting',
            'sort_no' => $stBrandSetting,
        ]);
        Permission::insert([
            [
                'display_name' => $view,
                'name' => 'brand-setting-view',
                'guard_name' => 'web',
                'module_id' => $brandSetting->id,
            ],
            [
                'display_name' => $create,
                'name' => 'brand-setting-create',
                'guard_name' => 'web',
                'module_id' => $brandSetting->id,
            ],
            [
                'display_name' => $edit,
                'name' => 'brand-setting-update',
                'guard_name' => 'web',
                'module_id' => $brandSetting->id,
            ],
        ]);
        //endBrand
        //Point
        $point = ModulePermission::create([
            'name' => 'Point',
            'parent_id' => $gpSetting,
            'parent_name' => 'Setting',
            'sort_no' => $stPoint,

        ]);
        Permission::insert([
            [
                'display_name' => $view,
                'name' => 'reward-view',
                'guard_name' => 'web',
                'module_id' => $point->id,
            ],
            [
                'display_name' => $create,
                'name' => 'reward-create',
                'guard_name' => 'web',
                'module_id' => $point->id,
            ],
            [
                'display_name' => $edit,
                'name' => 'reward-update',
                'guard_name' => 'web',
                'module_id' => $point->id,
            ],
        ]);
        //endPoint
        //Point
        $topUpRate = ModulePermission::create([
            'name' => 'Top Up Rate',
            'parent_id' => $gpSetting,
            'parent_name' => 'Setting',
            'sort_no' => $stTopUpRate,

        ]);
        Permission::insert([
            [
                'display_name' => $view,
                'name' => 'top-up-rate-view',
                'guard_name' => 'web',
                'module_id' => $topUpRate->id,
            ]
        ]);
        //endPoint

    }
    public function increaseIndex()
    {
        return $this->index += 1;
    }
}
