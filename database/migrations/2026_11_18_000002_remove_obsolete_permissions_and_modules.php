<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $obsoleteModules = [
            'Barber',
            'Wallet',
            'Service',
            'Promotion',
            'Banner',
            'Customer Point',
            'Report Transaction',
            'Report Summary',
            'About us',
            'Privacy & Policy',
            'Contact us',
            'Brand',
            'Brand Setting',
            'Point',
            'Top Up Rate',
            'Partner',
            'Sector',
            'Internships',
            'Blog',
            'Placement Type',
            'Testimonial',
            'Customer Paid',
        ];

        $modules = DB::table('module_permissions')->whereIn('name', $obsoleteModules)->get();
        $moduleIds = $modules->pluck('id')->all();

        if (!empty($moduleIds)) {
            $permissions = DB::table('permissions')->whereIn('module_id', $moduleIds)->get();
            $permIds = $permissions->pluck('id')->all();

            if (!empty($permIds)) {
                DB::table('model_has_permissions')->whereIn('permission_id', $permIds)->delete();
                DB::table('role_has_permissions')->whereIn('permission_id', $permIds)->delete();
                DB::table('permissions')->whereIn('id', $permIds)->delete();
            }

            DB::table('module_permissions')->whereIn('id', $moduleIds)->delete();
        }

        // Also ensure any leftover permissions with obsolete prefixes are removed
        $obsoletePrefixes = [
            'barber-', 'wallet-', 'service-', 'promotion-', 'slide-',
            'customer-point-', 'report-transaction-', 'report-summary-',
            'about-', 'privacy-', 'contact-', 'brand-', 'brand-setting-',
            'reward-', 'top-up-rate-', 'partner-', 'sector-', 'internships-',
            'blog-', 'placement-type-', 'testimonial-', 'customer-paid-',
        ];

        foreach ($obsoletePrefixes as $prefix) {
            $matchingPerms = DB::table('permissions')->where('name', 'like', $prefix . '%')->get();
            $permIds = $matchingPerms->pluck('id')->all();
            if (!empty($permIds)) {
                DB::table('model_has_permissions')->whereIn('permission_id', $permIds)->delete();
                DB::table('role_has_permissions')->whereIn('permission_id', $permIds)->delete();
                DB::table('permissions')->whereIn('id', $permIds)->delete();
            }
        }

        // Ensure Backup & Restore exists as an active module for setting
        $backupModule = DB::table('module_permissions')->where('name', 'Backup & Restore')->first();
        if (!$backupModule) {
            $backupModuleId = DB::table('module_permissions')->insertGetId([
                'name' => 'Backup & Restore',
                'parent_name' => 'Setting',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $backupModuleId = $backupModule->id;
        }

        if (!DB::table('permissions')->where('name', 'backup-view')->exists()) {
            DB::table('permissions')->insert([
                'name' => 'backup-view',
                'guard_name' => 'web',
                'display_name' => 'View',
                'module_id' => $backupModuleId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No reversal needed for removed dead/obsolete modules
    }
};
