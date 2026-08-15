<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('staff_expenses') && !Schema::hasColumn('staff_expenses', 'status')) {
            Schema::table('staff_expenses', function (Blueprint $table) {
                $table->tinyInteger('status')->default(1)->comment('1: Active, 2: Disabled')->after('created_by');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('staff_expenses') && Schema::hasColumn('staff_expenses', 'status')) {
            Schema::table('staff_expenses', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
