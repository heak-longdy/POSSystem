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
        Schema::create('barbers', function (Blueprint $table) {
            $table->id()->unique();
            $table->string('number_id')->nullable()->unique();
            $table->integer('shop_id')->nullable()->index();
            $table->string('name')->nullable();
            $table->string('gender')->nullable();
            $table->date('dob')->nullable();
            $table->string('phone')->unique();
            $table->text('address')->nullable();
            $table->double('commission')->nullable();
            $table->double('wallet')->default(0);
            $table->integer('status')->default(1);
            $table->text('image')->nullable();
            $table->string('password')->nullable();
            $table->string('type')->nullable();
            $table->string('code')->nullable();
            $table->boolean('is_point')->default(1);
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
        Schema::dropIfExists('barbers');
    }
};
