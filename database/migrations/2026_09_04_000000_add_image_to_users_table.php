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
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'image')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('image')->nullable()->after('status');
            });

            if (Schema::hasColumn('users', 'profile')) {
                DB::table('users')
                    ->whereNotNull('profile')
                    ->whereNull('image')
                    ->update([
                        'image' => DB::raw('profile')
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'image')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('image');
            });
        }
    }
};
