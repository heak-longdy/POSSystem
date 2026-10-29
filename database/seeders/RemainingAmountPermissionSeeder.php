<?php

namespace Database\Seeders;

use App\Models\ModulePermission;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RemainingAmountPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $module = ModulePermission::firstOrCreate(['name' => 'Remaining Amount']);

        foreach (['view' => 'View', 'update' => 'Edit', 'delete' => 'Delete'] as $action => $label) {
            Permission::updateOrCreate(
                ['name' => 'remaining-amount-' . $action, 'guard_name' => 'web'],
                ['display_name' => $label, 'module_id' => $module->id]
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
