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
        Schema::create('booking_details', function (Blueprint $table) {
            $table->id()->unique();
            $table->integer('booking_id')->index();
            $table->integer('service_id')->nullable()->index();
            $table->integer('product_id')->nullable()->index();
            $table->double('price')->default(0);
            $table->integer('qty')->default(1);
            $table->double('point')->nullable();
            $table->string('type')->nullable()->index();
            $table->double('product_discount')->default(0);
            $table->string('product_discount_type')->nullable();
            $table->double('service_discount')->default(0);
            $table->string('service_discount_type')->nullable();
            $table->double('service_commission')->default(0);
            $table->string('service_commission_type')->nullable();
            $table->double('product_commission')->default(0);
            $table->string('product_commission_type')->nullable();
            $table->text('remark')->nullable();
            $table->double('rate')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('booking_details');
    }
};
