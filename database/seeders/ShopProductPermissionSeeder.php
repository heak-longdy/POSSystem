<?php

namespace Database\Seeders;

use App\Models\ModulePermission;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class ShopProductPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $module = ModulePermission::firstOrCreate(['name' => 'Shop']);

        Permission::updateOrCreate(
            ['name' => 'shop-product', 'guard_name' => 'web'],
            ['display_name' => 'Product', 'module_id' => $module->id]
        );

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
