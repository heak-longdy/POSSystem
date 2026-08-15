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
        Schema::create('shop_services', function (Blueprint $table) {
            $table->id()->unique();
            $table->integer('shop_id')->index();
            $table->integer('service_id')->index();
            $table->double('price')->nullable();
            $table->double('point')->nullable();
            $table->double('commission')->nullable();
            $table->string('commission_type')->nullable();
            $table->string('type')->nullable();
            $table->double('discount')->nullable();
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->integer('status')->default(1);
            $table->timestamps();

            $table->unique(['shop_id', 'service_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_services');
    }
};
