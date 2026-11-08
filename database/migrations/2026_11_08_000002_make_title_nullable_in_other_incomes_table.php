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
        if (Schema::hasTable('other_incomes') && Schema::hasColumn('other_incomes', 'title')) {
            Schema::table('other_incomes', function (Blueprint $table) {
                $table->string('title', 255)->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('other_incomes') && Schema::hasColumn('other_incomes', 'title')) {
            Schema::table('other_incomes', function (Blueprint $table) {
                $table->string('title', 255)->nullable(false)->change();
            });
        }
    }
};

