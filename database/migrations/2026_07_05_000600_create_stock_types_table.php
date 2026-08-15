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
        Schema::create('stock_types', function (Blueprint $table) {
            $table->id()->unique();
            $table->integer('key')->unique();
            $table->string('name');
            $table->integer('ordering')->nullable();
            $table->integer('user_id')->nullable()->index();
            $table->integer('status')->default(1);
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
        Schema::dropIfExists('stock_types');
    }
};
