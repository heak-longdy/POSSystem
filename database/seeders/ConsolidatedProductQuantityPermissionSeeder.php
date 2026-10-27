<?php

namespace Database\Seeders;

use App\Models\ModulePermission;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class ConsolidatedProductQuantityPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $module = ModulePermission::firstOrCreate(
            ['name' => 'Consolidated Product Quantity'],
            ['parent_name' => 'Report Management', 'sort_no' => 203]
        );
        foreach (['view' => 'View', 'excel' => 'Excel'] as $action => $label) {
            Permission::firstOrCreate(
                ['name' => 'report-consolidated-product-quantity-' . $action, 'guard_name' => 'web'],
                ['display_name' => $label, 'module_id' => $module->id]
            );
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
