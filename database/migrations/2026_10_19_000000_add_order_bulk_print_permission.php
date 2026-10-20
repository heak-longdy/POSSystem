<?php

use Database\Seeders\OrderBulkPrintPermissionSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::transaction(fn () => (new OrderBulkPrintPermissionSeeder())->run());
    }

    public function down()
    {
        // Preserve permissions that may already have been assigned to users.
    }
};
