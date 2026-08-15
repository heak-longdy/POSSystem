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
        Schema::create('customer_points', function (Blueprint $table) {
            $table->id()->unique();
            $table->integer('customer_id')->index();
            $table->integer('shop_id')->nullable()->index();
            $table->integer('brand_id')->nullable()->index();
            $table->integer('total_point')->default(0);
            $table->integer('total_receving_point')->default(0);
            $table->integer('used_point')->default(0);
            $table->integer('count_of_using_service')->default(0);
            $table->timestamps();

            $table->index(['shop_id', 'customer_id']);
            $table->index(['brand_id', 'customer_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_points');
    }
};
