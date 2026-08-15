<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_histories', function (Blueprint $table) {
            $table->id()->unique();
            $table->integer('transfer_id')->nullable()->index();
            $table->integer('stock_id')->nullable()->index();
            $table->integer('product_id')->index();
            $table->integer('current_stock')->default(0);
            $table->integer('stock_in')->default(0);
            $table->integer('stock_out')->default(0);
            $table->integer('shop_id')->index();
            $table->integer('to_id')->nullable()->index();
            $table->integer('qty')->default(0);
            $table->text('remark')->nullable();
            $table->string('status')->nullable()->index();
            $table->string('type')->nullable();
            $table->string('transfer_type')->nullable();
            $table->integer('request_by')->nullable()->index();
            $table->string('request_by_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_histories');
    }
};
