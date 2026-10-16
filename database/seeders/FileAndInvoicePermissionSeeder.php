<?php

namespace Database\Seeders;

use App\Models\ModulePermission;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class FileAndInvoicePermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'file-manager' => ['File Manager', ['view' => 'View', 'create' => 'Create', 'update' => 'Edit', 'delete' => 'Delete']],
            'setting-invoice' => ['Invoice Setting', ['view' => 'View', 'update' => 'Edit']],
        ] as $prefix => [$name, $actions]) {
            $module = ModulePermission::firstOrCreate(['name' => $name]);
            foreach ($actions as $action => $label) {
                Permission::firstOrCreate(
                    ['name' => $prefix . '-' . $action, 'guard_name' => 'web'],
                    ['display_name' => $label, 'module_id' => $module->id]
                );
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
