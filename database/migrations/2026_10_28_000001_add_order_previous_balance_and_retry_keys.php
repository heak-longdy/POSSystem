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
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('previous_balance', 15, 2)->default(0.00)->after('customer_id');
            $table->char('creation_request_key', 36)->nullable()->unique()->after('remark');
            $table->char('creation_payload_hash', 64)->nullable()->after('creation_request_key');
            $table->index(['customer_id', 'deleted_at', 'payment_status', 'id'], 'orders_balance_candidates_idx');
        });

        Schema::table('order_payments', function (Blueprint $table) {
            $table->char('payment_request_key', 36)->nullable()->unique()->after('note');
            $table->char('payment_payload_hash', 64)->nullable()->after('payment_request_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_payments', function (Blueprint $table) {
            $table->dropUnique(['payment_request_key']);
            $table->dropColumn(['payment_request_key', 'payment_payload_hash']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_balance_candidates_idx');
            $table->dropUnique(['creation_request_key']);
            $table->dropColumn(['previous_balance', 'creation_request_key', 'creation_payload_hash']);
        });
    }
};
