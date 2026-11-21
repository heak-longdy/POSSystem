<?php

use App\Models\ModulePermission;
use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $module = ModulePermission::where('name', 'Customer')->first();
            if (!$module) {
                $module = ModulePermission::create([
                    'name' => 'Customer',
                    'parent_id' => null,
                    'sort_no' => 3,
                ]);
            }

            $detail = Permission::where('name', 'customer-detail')->first();
            if (!$detail) {
                Permission::create([
                    'name' => 'customer-detail',
                    'guard_name' => 'web',
                    'display_name' => 'View Detail',
                    'module_id' => $module->id,
                ]);
            } else if (!$detail->module_id) {
                $detail->update(['module_id' => $module->id]);
            }

            // Also ensure customer-delete has module_id if present
            $delete = Permission::where('name', 'customer-delete')->first();
            if ($delete && !$delete->module_id) {
                $delete->update(['module_id' => $module->id]);
            }
        });

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Preserve permissions that may already have been assigned to users.
    }
};
