<?php

namespace Database\Seeders;

use App\Models\ModulePermission;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class PurchasePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Supplier Purchasing module
        $purchaseModule = ModulePermission::firstOrCreate(
            ['name' => 'Supplier Purchasing'],
            ['sort_no' => 210]
        );

        $purchaseActions = [
            'view'         => 'View',
            'create'       => 'Create',
            'update'       => 'Edit',
            'delete'       => 'Delete',
            'restore'      => 'Restore',
            'force-delete' => 'Destroy',
            'receive'      => 'Receive Goods',
            'payment'      => 'Payment',
            'excel'        => 'Excel',
            'print'        => 'Print',
        ];

        foreach ($purchaseActions as $action => $label) {
            Permission::firstOrCreate(
                ['name' => 'purchase-' . $action, 'guard_name' => 'web'],
                ['display_name' => $label, 'module_id' => $purchaseModule->id]
            );
        }

        // 2. Purchase Report module
        $reportModule = ModulePermission::firstOrCreate(
            ['name' => 'Purchase Report'],
            ['parent_name' => 'Report Management', 'sort_no' => 211]
        );

        foreach (['view' => 'View', 'excel' => 'Excel'] as $action => $label) {
            Permission::firstOrCreate(
                ['name' => 'report-purchase-' . $action, 'guard_name' => 'web'],
                ['display_name' => $label, 'module_id' => $reportModule->id]
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
