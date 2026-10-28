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
        Schema::create('order_balance_transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('new_order_id');
            $table->unsignedBigInteger('previous_order_id');
            $table->decimal('transferred_amount', 15, 2);
            $table->string('original_payment_status', 20);
            $table->dateTime('transferred_at');
            $table->unsignedBigInteger('transferred_by')->nullable();
            $table->string('transfer_status', 20)->default('completed');
            $table->dateTime('reversed_at')->nullable();
            $table->unsignedBigInteger('reversed_by')->nullable();
            $table->string('reversal_reason', 500)->nullable();

            $table->unsignedBigInteger('active_source_order_id')
                ->storedAs("CASE WHEN transfer_status = 'completed' THEN previous_order_id ELSE NULL END")
                ->nullable();

            $table->timestamps();

            $table->unique('active_source_order_id', 'obt_one_active_source');
            $table->unique(['previous_order_id', 'new_order_id'], 'obt_source_destination');
            $table->index(['new_order_id', 'transfer_status'], 'obt_destination_status');
            $table->index(['previous_order_id', 'transferred_at'], 'obt_source_history');

            $table->foreign('new_order_id', 'obt_destination_fk')
                ->references('id')->on('orders')->onDelete('restrict');
            $table->foreign('previous_order_id', 'obt_source_fk')
                ->references('id')->on('orders')->onDelete('restrict');
            $table->foreign('transferred_by', 'obt_actor_fk')
                ->references('id')->on('users')->onDelete('set null');
            $table->foreign('reversed_by', 'obt_reversal_actor_fk')
                ->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_balance_transfers');
    }
};
