<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    // public function up()
    // {
    //     Schema::create('users', function (Blueprint $table) {
    //         $table->increments('id');
    //         $table->string('name');
    //         $table->text('profile')->nullable();
    //         $table->string('gender')->nullable();
    //         $table->string('email',100)->unique()->nullable();
    //         $table->string('phone',100)->unique()->nullable();
    //         $table->string('emergency_phone', 100)->unique()->nullable();
    //         $table->timestamp('email_verified_at')->nullable();
    //         $table->string('password');
    //         $table->tinyInteger('status');
    //         $table->string('role')->nullable();
    //         $table->string('type')->comment('user,member,garage');
    //         $table->longText('des')->nullable();
    //         $table->longText('address')->nullable();
    //         $table->string('map')->nullable()->comment('[latitude,longitude]');
    //         $table->integer('user_id')->nullable();
    //         $table->rememberToken();
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
    //     Schema::dropIfExists('users');
    // }
}
