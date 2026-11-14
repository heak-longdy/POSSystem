<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\ModulePermission;
use App\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('backup_settings')) {
            Schema::create('backup_settings', function (Blueprint $table) {
                $table->id();
                $table->string('cloud_driver', 30)->default('s3'); // 's3', 'r2', 'minio', etc.
                $table->string('s3_endpoint', 255)->nullable();
                $table->string('s3_region', 50)->default('us-east-1');
                $table->string('s3_bucket', 100)->nullable();
                $table->text('s3_access_key')->nullable();
                $table->text('s3_secret_key')->nullable();
                $table->string('s3_path_prefix', 100)->default('backups/');
                $table->boolean('s3_use_path_style')->default(false);
                $table->boolean('auto_backup_enabled')->default(false);
                $table->string('auto_backup_frequency', 20)->default('daily');
                $table->boolean('auto_upload_cloud')->default(false);
                $table->unsignedInteger('max_local_backups')->default(15);
                $table->unsignedInteger('disk_quota_gb')->default(50);
                $table->timestamps();
            });

            // Initialize default record
            DB::table('backup_settings')->insert([
                'cloud_driver'         => 's3',
                's3_region'            => env('AWS_DEFAULT_REGION', 'us-east-1'),
                's3_bucket'            => env('AWS_BUCKET', ''),
                's3_access_key'        => env('AWS_ACCESS_KEY_ID', ''),
                's3_secret_key'        => env('AWS_SECRET_ACCESS_KEY', ''),
                's3_path_prefix'       => 'backups/',
                's3_use_path_style'    => (bool) env('AWS_USE_PATH_STYLE_ENDPOINT', false),
                'auto_backup_enabled'  => false,
                'auto_backup_frequency'=> 'daily',
                'auto_upload_cloud'    => false,
                'max_local_backups'    => 15,
                'disk_quota_gb'        => 50,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
        }

        if (!Schema::hasTable('backup_records')) {
            Schema::create('backup_records', function (Blueprint $table) {
                $table->id();
                $table->string('name', 150)->nullable();
                $table->string('filename', 255)->unique();
                $table->string('file_path', 255);
                $table->string('disk', 30)->default('local'); // 'local', 'cloud', 'both'
                $table->string('type', 30)->default('full'); // 'full', 'database'
                $table->unsignedBigInteger('size_bytes')->default(0);
                $table->unsignedInteger('tables_count')->default(0);
                $table->unsignedInteger('files_count')->default(0);
                $table->string('status', 30)->default('completed'); // 'completed', 'failed', 'restored'
                $table->string('cloud_status', 30)->default('none'); // 'none', 'uploaded', 'failed'
                $table->timestamp('cloud_uploaded_at')->nullable();
                $table->timestamp('last_restored_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // Register module permissions for Backup & Restore
        // try {
        //     $module = ModulePermission::firstOrCreate(['name' => 'Backup & Restore']);
        //     $actions = [
        //         'view'     => 'View',
        //         'create'   => 'Create Backup',
        //         'restore'  => 'Restore Backup',
        //         'download' => 'Download Backup',
        //         'update'   => 'Settings',
        //         'delete'   => 'Delete Backup',
        //     ];

        //     foreach ($actions as $action => $label) {
        //         Permission::firstOrCreate(
        //             ['name' => 'backup-' . $action, 'guard_name' => 'web'],
        //             ['display_name' => $label, 'module_id' => $module->id]
        //         );
        //     }

        //     app(PermissionRegistrar::class)->forgetCachedPermissions();
        // } catch (\Throwable $e) {
        //     // Permission tables might not exist or cache issue during initial migration
        // }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backup_records');
        Schema::dropIfExists('backup_settings');
    }
};

