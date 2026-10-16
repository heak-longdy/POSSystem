<?php

namespace Database\Seeders;

use App\Models\ModulePermission;
use App\Models\Permission;
use Illuminate\Database\Seeder;

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
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $view = "View";
        $create = "Create";
        $edit = "Edit";
        $delete = "Delete";
        $trash = "Trash";
        $destroy = "Destroy";
        $excel = "Excel";
        $reportExcel = "Report Excel";

        $stDashboard =  $this->increaseIndex();
        $stOrder =  $this->increaseIndex();
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
        $stReportSales = $this->increaseIndex();
        $stReportInventory = $this->increaseIndex();

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
        $dashboard = $this->module([
            'name' => 'Dashboard',
            'parent_id' => $stDashboard,
            'sort_no' => $stDashboard,
        ]);
        $this->permission([
            'display_name' => $view,
            'name' => 'dashboard-view',
            'guard_name' => 'web',
            'module_id' => $dashboard->id,
        ]);

        //Order
        $order = $this->module([
            'name' => 'Order',
            'parent_id' => $stOrder,
            'sort_no' => $stOrder,

        ]);
        $this->permissions([
            [
                'display_name' => $view,
                'name' => 'order-view',
                'guard_name' => 'web',
                'module_id' => $order->id,
            ],
            [
                'display_name' => $create,
                'name' => 'order-create',
                'guard_name' => 'web',
                'module_id' => $order->id,
            ],
            [
                'display_name' => $edit,
                'name' => 'order-update',
                'guard_name' => 'web',
                'module_id' => $order->id,
            ],
            [
                'display_name' => $delete,
                'name' => 'order-delete',
                'guard_name' => 'web',
                'module_id' => $order->id,
            ],
        ]);
        
        //end Order

        //Customer
        $customer = $this->module([
            'name' => 'Customer',
            'parent_id' => $stCustomer,
            'sort_no' => $stCustomer,

        ]);
        $this->permissions([
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
        $shop = $this->module([
            'name' => 'Shop',
            'parent_id' => $stShop,
            'sort_no' => $stShop,

        ]);
        $this->permissions([
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
        $barber = $this->module([
            'name' => 'Barber',
            'parent_id' => $stBarber,
            'sort_no' => $stBarber,

        ]);
        $this->permissions([
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
        $wallet = $this->module([
            'name' => 'Wallet',
            'parent_id' => $stWallet,
            'sort_no' => $stWallet,

        ]);
        $this->permissions([
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
        $product = $this->module([
            'name' => 'Product',
            'parent_id' => $stProduct,
            'sort_no' => $stProduct,

        ]);
        $this->permissions([
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
        $service = $this->module([
            'name' => 'Service',
            'parent_id' => $stService,
            'sort_no' => $stService,

        ]);
        $this->permissions([
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
        $promotion = $this->module([
            'name' => 'Promotion',
            'parent_id' => $stPromotion,
            'sort_no' => $stPromotion,

        ]);
        $this->permissions([
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
        $slide = $this->module([
            'name' => 'Banner',
            'parent_id' => $stSlide,
            'sort_no' => $stSlide,

        ]);
        $this->permissions([
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
        $user = $this->module([
            'name' => 'User',
            'parent_id' => $stUser,
            'sort_no' => $stUser,

        ]);
        $this->permissions([
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
        $customerPoint = $this->module([
            'name' => 'Customer Point',
            'parent_id' => $stCustomerPoint,
            'sort_no' => $stCustomerPoint,

        ]);
        $this->permissions([
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
        $reportTransaction = $this->module([
            'name' => 'Report Transaction',
            'parent_id' => $stReportTransaction,
            'sort_no' => $stReportTransaction,

        ]);
        $this->permissions([
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
        $reportSummary = $this->module([
            'name' => 'Report Summary',
            'parent_id' => $stReportSummary,
            'sort_no' => $stReportSummary,

        ]);
        $this->permissions([
            [
                'display_name' => $view,
                'name' => 'report-summary-view',
                'guard_name' => 'web',
                'module_id' => $reportSummary->id,
            ]
        ]);
        //endReportSummary

        //reportSales
        $reportSales = $this->module([
            'name' => 'Report Sales',
            'parent_id' => $stReportSales,
            'sort_no' => $stReportSales,
        ]);
        $this->permissions([
            [
                'display_name' => $view,
                'name' => 'report-sales-view',
                'guard_name' => 'web',
                'module_id' => $reportSales->id,
            ],
            [
                'display_name' => $excel,
                'name' => 'report-sales-excel',
                'guard_name' => 'web',
                'module_id' => $reportSales->id,
            ]
        ]);
        //endReportSales

        //reportInventory
        $reportInventory = $this->module([
            'name' => 'Report Inventory Movement',
            'parent_id' => $stReportInventory,
            'sort_no' => $stReportInventory,
        ]);
        $this->permissions([
            [
                'display_name' => $view,
                'name' => 'report-inventory-view',
                'guard_name' => 'web',
                'module_id' => $reportInventory->id,
            ],
            [
                'display_name' => $excel,
                'name' => 'report-inventory-excel',
                'guard_name' => 'web',
                'module_id' => $reportInventory->id,
            ]
        ]);
        //endReportInventory


        // InventoryManagement
        // StockIn
        $stockIn = $this->module([
            'name' => 'Stock In',
            'parent_id' => $gpInventoryManagement,
            'parent_name' => 'Inventory Management',
            'sort_no' => $stStockIn,
        ]);
        $this->permissions([
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
        $stockOut = $this->module([
            'name' => 'Stock Out',
            'parent_id' => $gpInventoryManagement,
            'parent_name' => 'Inventory Management',
            'sort_no' => $stStockOut,
        ]);
        $this->permissions([
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
        $stockTransfer = $this->module([
            'name' => 'Stock Transfer',
            'parent_id' => $gpInventoryManagement,
            'parent_name' => 'Inventory Management',
            'sort_no' => $stStockTransfer,
        ]);
        $this->permissions([
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
        $stockOnHand = $this->module([
            'name' => 'Stock On Hand',
            'parent_id' => $gpInventoryManagement,
            'parent_name' => 'Inventory Management',
            'sort_no' => $stStockOnHand,
        ]);
        $this->permissions([
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
        $stockMovement = $this->module([
            'name' => 'Stock Movement',
            'parent_id' => $gpInventoryManagement,
            'parent_name' => 'Inventory Management',
            'sort_no' => $stStockMovement,
        ]);
        $this->permissions([
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
        $aboutUs = $this->module([
            'name' => 'About us',
            'parent_id' => $gpPage,
            'parent_name' => 'Page Management',
            'sort_no' => $stAbout,

        ]);
        $this->permissions([
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
        $policy = $this->module([
            'name' => 'Privacy & Policy',
            'parent_id' => $gpPage,
            'parent_name' => 'Page Management',
            'sort_no' => $stPolicy,

        ]);
        $this->permissions([
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
        $contactUs = $this->module([
            'name' => 'Contact us',
            'parent_id' => $gpPage,
            'parent_name' => 'Page Management',
            'sort_no' => $stContact,

        ]);
        $this->permissions([
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
        $category = $this->module([
            'name' => 'Category',
            'parent_id' => $gpSetting,
            'parent_name' => 'Setting',
            'sort_no' => $stCategory,

        ]);
        $this->permissions([
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
        $supplier = $this->module([
            'name' => 'Supplier',
            'parent_id' => $gpSetting,
            'parent_name' => 'Setting',
            'sort_no' => $stSupplier,

        ]);
        $this->permissions([
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
        $uom = $this->module([
            'name' => 'UOM',
            'parent_id' => $gpSetting,
            'parent_name' => 'Setting',
            'sort_no' => $stUom,

        ]);
        $this->permissions([
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
        $brand = $this->module([
            'name' => 'Brand',
            'parent_id' => $gpSetting,
            'parent_name' => 'Setting',
            'sort_no' => $stBrand,

        ]);
        $this->permissions([
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
        $brandSetting = $this->module([
            'name' => 'Brand Setting',
            'parent_id' => $gpSetting,
            'parent_name' => 'Setting',
            'sort_no' => $stBrandSetting,
        ]);
        $this->permissions([
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
        $point = $this->module([
            'name' => 'Point',
            'parent_id' => $gpSetting,
            'parent_name' => 'Setting',
            'sort_no' => $stPoint,

        ]);
        $this->permissions([
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
        $topUpRate = $this->module([
            'name' => 'Top Up Rate',
            'parent_id' => $gpSetting,
            'parent_name' => 'Setting',
            'sort_no' => $stTopUpRate,

        ]);
        $this->permissions([
            [
                'display_name' => $view,
                'name' => 'top-up-rate-view',
                'guard_name' => 'web',
                'module_id' => $topUpRate->id,
            ]
        ]);
        //Staff Expense
        $stStaffExpense = $this->increaseIndex();
        $staffExpense = $this->module([
            'name' => 'Staff Expense',
            'parent_id' => $stStaffExpense,
            'sort_no' => $stStaffExpense,
        ]);
        $this->permissions([
            [
                'display_name' => $view,
                'name' => 'staff-expense-view',
                'guard_name' => 'web',
                'module_id' => $staffExpense->id,
            ],
            [
                'display_name' => $create,
                'name' => 'staff-expense-create',
                'guard_name' => 'web',
                'module_id' => $staffExpense->id,
            ],
            [
                'display_name' => $edit,
                'name' => 'staff-expense-update',
                'guard_name' => 'web',
                'module_id' => $staffExpense->id,
            ],
            [
                'display_name' => $delete,
                'name' => 'staff-expense-delete',
                'guard_name' => 'web',
                'module_id' => $staffExpense->id,
            ],
            [
                'display_name' => $trash,
                'name' => 'staff-expense-restore',
                'guard_name' => 'web',
                'module_id' => $staffExpense->id,
            ],
            [
                'display_name' => $destroy,
                'name' => 'staff-expense-force-delete',
                'guard_name' => 'web',
                'module_id' => $staffExpense->id,
            ],
        ]);

        $this->completeActiveModules();
        (new FileAndInvoicePermissionSeeder())->run();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
    private function module(array $attributes)
    {
        return ModulePermission::firstOrCreate(['name' => $attributes['name']], $attributes);
    }

    private function permission(array $attributes)
    {
        return Permission::updateOrCreate(
            ['name' => $attributes['name'], 'guard_name' => $attributes['guard_name']],
            $attributes
        );
    }

    private function permissions(array $permissions): void
    {
        foreach ($permissions as $permission) {
            $this->permission($permission);
        }
    }

    private function completeActiveModules(): void
    {
        $labels = ['view' => 'View', 'create' => 'Create', 'update' => 'Edit',
            'delete' => 'Delete', 'restore' => 'Restore', 'force-delete' => 'Destroy', 'excel' => 'Excel'];
        foreach (config('admin_permissions.modules') as $prefix => $name) {
            $module = $this->module(['name' => $name, 'sort_no' => $this->increaseIndex()]);
            $actions = array_merge(['view', 'create', 'update', 'delete'], config('admin_permissions.extra_actions.' . $prefix, []));
            foreach ($actions as $action) {
                $this->permission(['name' => $prefix . '-' . $action, 'guard_name' => 'web',
                    'display_name' => $labels[$action], 'module_id' => $module->id]);
            }
        }
        $module = $this->module(['name' => 'Staff Expense Report', 'parent_name' => 'Report Management', 'sort_no' => $this->increaseIndex()]);
        foreach (['view', 'excel'] as $action) {
            $this->permission(['name' => 'report-staff-expense-' . $action, 'guard_name' => 'web',
                'display_name' => $labels[$action], 'module_id' => $module->id]);
        }
    }

    public function increaseIndex()
    {
        return $this->index += 1;
    }
}
