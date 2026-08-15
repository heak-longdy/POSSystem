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
        Schema::create('shop_products', function (Blueprint $table) {
            $table->id()->unique();
            $table->integer('shop_id')->index();
            $table->integer('product_id')->index();
            $table->integer('promotion_id')->nullable()->index();
            $table->double('price')->nullable();
            $table->integer('qty')->nullable();
            $table->integer('max_qty')->nullable();
            $table->double('point')->nullable();
            $table->double('commission')->nullable();
            $table->string('commission_type')->nullable();
            $table->integer('status')->default(1);
            $table->timestamps();

            $table->unique(['shop_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_products');
    }
};
