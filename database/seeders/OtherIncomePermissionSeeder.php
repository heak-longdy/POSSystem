<?php

namespace Database\Seeders;

use App\Models\ModulePermission;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class OtherIncomePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Other Income module
        $incomeModule = ModulePermission::firstOrCreate(
            ['name' => 'Other Income'],
            ['sort_no' => 200]
        );

        $incomeActions = [
            'view'         => 'View',
            'create'       => 'Create',
            'update'       => 'Edit',
            'delete'       => 'Delete',
            'restore'      => 'Restore',
            'force-delete' => 'Destroy',
            'excel'        => 'Excel',
        ];

        foreach ($incomeActions as $action => $label) {
            Permission::firstOrCreate(
                ['name' => 'other-income-' . $action, 'guard_name' => 'web'],
                ['display_name' => $label, 'module_id' => $incomeModule->id]
            );
        }

        // 2. Other Income Category module
        $catModule = ModulePermission::firstOrCreate(
            ['name' => 'Other Income Category'],
            ['sort_no' => 201]
        );

        $catActions = [
            'view'   => 'View',
            'create' => 'Create',
            'update' => 'Edit',
            'delete' => 'Delete',
        ];

        foreach ($catActions as $action => $label) {
            Permission::firstOrCreate(
                ['name' => 'other-income-category-' . $action, 'guard_name' => 'web'],
                ['display_name' => $label, 'module_id' => $catModule->id]
            );
        }

        // 3. Other Income Report module
        $reportModule = ModulePermission::firstOrCreate(
            ['name' => 'Other Income Report'],
            ['parent_name' => 'Report Management', 'sort_no' => 202]
        );

        foreach (['view' => 'View', 'excel' => 'Excel'] as $action => $label) {
            Permission::firstOrCreate(
                ['name' => 'report-other-income-' . $action, 'guard_name' => 'web'],
                ['display_name' => $label, 'module_id' => $reportModule->id]
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
