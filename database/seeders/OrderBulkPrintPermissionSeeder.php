<?php

namespace Database\Seeders;

use App\Models\ModulePermission;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class OrderBulkPrintPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $module = ModulePermission::firstOrCreate(['name' => 'Order Bulk Print']);

        Permission::firstOrCreate(
            ['name' => 'order-bulk-print-view', 'guard_name' => 'web'],
            ['display_name' => 'View', 'module_id' => $module->id]
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
