<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('purchases')) {
            DB::statement('ALTER TABLE purchases MODIFY purchase_number VARCHAR(50) NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('purchases')) {
            DB::statement('ALTER TABLE purchases MODIFY purchase_number VARCHAR(50) NOT NULL');
        }
    }
};
