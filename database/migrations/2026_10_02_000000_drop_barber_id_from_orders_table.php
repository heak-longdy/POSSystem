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
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'barber_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('barber_id');
            });
        }

        if (Schema::hasTable('bookings') && Schema::hasColumn('bookings', 'barber_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('barber_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orders') && !Schema::hasColumn('orders', 'barber_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->integer('barber_id')->nullable()->index();
            });
        }

        if (Schema::hasTable('bookings') && !Schema::hasColumn('bookings', 'barber_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->integer('barber_id')->nullable()->index();
            });
        }
    }
};
