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
        Schema::create('inventory_histories', function (Blueprint $table) {
            $table->id()->unique();
            $table->integer('shop_id')->index();
            $table->integer('shop_product_id')->index();
            $table->integer('start_qty')->default(0);
            $table->integer('end_qty')->default(0);
            $table->integer('stock_qty')->default(0);
            $table->text('remark')->nullable();
            $table->dateTime('history_date')->nullable();
            $table->string('type')->nullable()->index();
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
        Schema::dropIfExists('inventory_histories');
    }
};
