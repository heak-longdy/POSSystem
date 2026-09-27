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
        // 1. Safely backup legacy empty/unused orders table if present
        if (Schema::hasTable('orders') && !Schema::hasTable('orders_legacy_backup')) {
            Schema::rename('orders', 'orders_legacy_backup');
        }
        if (Schema::hasTable('order_details') && !Schema::hasTable('order_details_legacy_backup')) {
            Schema::rename('order_details', 'order_details_legacy_backup');
        }

        // 2. Rename bookings tables to orders tables
        if (Schema::hasTable('bookings') && !Schema::hasTable('orders')) {
            Schema::rename('bookings', 'orders');
        }

        if (Schema::hasTable('booking_details') && !Schema::hasTable('order_details')) {
            Schema::rename('booking_details', 'order_details');
        }

        if (Schema::hasTable('booking_payments') && !Schema::hasTable('order_payments')) {
            Schema::rename('booking_payments', 'order_payments');
        }

        // 3. Rename foreign keys and date columns if present
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'booking_date') && !Schema::hasColumn('orders', 'order_date')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->renameColumn('booking_date', 'order_date');
            });
        }

        if (Schema::hasTable('order_details') && Schema::hasColumn('order_details', 'booking_id') && !Schema::hasColumn('order_details', 'order_id')) {
            Schema::table('order_details', function (Blueprint $table) {
                $table->renameColumn('booking_id', 'order_id');
            });
        }

        if (Schema::hasTable('order_payments') && Schema::hasColumn('order_payments', 'booking_id') && !Schema::hasColumn('order_payments', 'order_id')) {
            Schema::table('order_payments', function (Blueprint $table) {
                $table->renameColumn('booking_id', 'order_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'order_date') && !Schema::hasColumn('orders', 'booking_date')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->renameColumn('order_date', 'booking_date');
            });
        }

        if (Schema::hasTable('order_details') && Schema::hasColumn('order_details', 'order_id') && !Schema::hasColumn('order_details', 'booking_id')) {
            Schema::table('order_details', function (Blueprint $table) {
                $table->renameColumn('order_id', 'booking_id');
            });
        }

        if (Schema::hasTable('order_payments') && Schema::hasColumn('order_payments', 'order_id') && !Schema::hasColumn('order_payments', 'booking_id')) {
            Schema::table('order_payments', function (Blueprint $table) {
                $table->renameColumn('order_id', 'booking_id');
            });
        }

        if (Schema::hasTable('order_payments') && !Schema::hasTable('booking_payments')) {
            Schema::rename('order_payments', 'booking_payments');
        }

        if (Schema::hasTable('order_details') && !Schema::hasTable('booking_details')) {
            Schema::rename('order_details', 'booking_details');
        }

        if (Schema::hasTable('orders') && !Schema::hasTable('bookings')) {
            Schema::rename('orders', 'bookings');
        }

        if (Schema::hasTable('orders_legacy_backup') && !Schema::hasTable('orders')) {
            Schema::rename('orders_legacy_backup', 'orders');
        }

        if (Schema::hasTable('order_details_legacy_backup') && !Schema::hasTable('order_details')) {
            Schema::rename('order_details_legacy_backup', 'order_details');
        }
    }
};
