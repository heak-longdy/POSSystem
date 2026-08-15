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
        Schema::create('folders', function (Blueprint $table) {
            $table->id()->unique();
            $table->integer('user_id')->nullable();
            $table->string('name');
            $table->integer('parent_id')->nullable();
            $table->integer('is_hidden')->default(0);
            $table->string('color_code')->nullable()->comment('Example: #ffffff');
            $table->integer('shortcut')->nullable()->comment('id of redirect folder');
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
        Schema::dropIfExists('folders');
    }
};
