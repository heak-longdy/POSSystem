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
    // public function up()
    // {
    //     Schema::create('slides', function (Blueprint $table) {
    //         $table->increments('id');
    //         $table->json('name')->nullable()->comment('{en: "", km: "", zh: ""}');
    //         $table->text('link')->nullable();
    //         $table->string('slug')->nullable();
    //         $table->integer('ordering')->nullable();
    //         $table->integer('status')->nullable();
    //         $table->text('image')->nullable();
    //         $table->integer('user_id')->nullable();
    //         $table->softDeletes();
    //         $table->timestamps();
    //     });
    // }

    // /**
    //  * Reverse the migrations.
    //  *
    //  * @return void
    //  */
    // public function down()
    // {
    //     Schema::dropIfExists('slides');
    // }
};
