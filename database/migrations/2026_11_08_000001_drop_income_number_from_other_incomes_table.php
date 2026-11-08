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
        if (Schema::hasTable('other_incomes')) {
            Schema::table('other_incomes', function (Blueprint $table) {
                if (Schema::hasColumn('other_incomes', 'income_number')) {
                    $table->dropColumn('income_number');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('other_incomes')) {
            Schema::table('other_incomes', function (Blueprint $table) {
                if (!Schema::hasColumn('other_incomes', 'income_number')) {
                    $table->string('income_number', 50)->nullable()->unique()->after('id');
                }
            });
        }
    }
};

