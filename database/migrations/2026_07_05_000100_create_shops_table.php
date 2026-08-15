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
        Schema::create('shops', function (Blueprint $table) {
            $table->id()->unique();
            $table->integer('type_id')->nullable()->index();
            $table->integer('brand_id')->nullable()->index();
            $table->integer('barber_id')->nullable()->index();
            $table->string('name')->nullable();
            $table->string('nick_name')->nullable();
            $table->string('phone')->unique();
            $table->text('address')->nullable();
            $table->text('image')->nullable();
            $table->string('password')->nullable();
            $table->double('total_wallet')->default(0);
            $table->integer('total_point')->default(0);
            $table->integer('status')->default(1);
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
        Schema::dropIfExists('shops');
    }
};
