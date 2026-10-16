<?php

use Database\Seeders\PermissionSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Add missing actions without truncating IDs or existing assignments.
        DB::transaction(fn () => (new PermissionSeeder())->run());
    }

    public function down()
    {
        // Keep permissions that may have been assigned since this migration.
    }
};
