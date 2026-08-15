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
        Schema::create('file_managers', function (Blueprint $table) {
            $table->id()->unique();
            $table->integer('user_id')->nullable();
            $table->integer('folder_id')->nullable();
            $table->string('name')->nullable();
            $table->string('path')->nullable();
            $table->string('size')->nullable()->comment('File size, in bytes');
            $table->double('width')->nullable()->comment('width of image,in pixels');
            $table->double('height')->nullable()->comment('height of image,in pixels');
            $table->string('extension')->nullable()->comment('extension of file, not including dot');
            $table->boolean('is_hidden')->default(0)->comment("1 if file is hidden, 0 if not");
            $table->boolean('is_image')->default(0)->comment("1 if file is image, 0 if not");
            $table->boolean('is_audio')->default(0)->comment("1 if file is audio, 0 if not");
            $table->boolean('is_video')->default(0)->comment("1 if file is video, 0 if not");
            $table->boolean('is_document')->default(0)->comment("1 if file is document, 0 if not");
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
        Schema::dropIfExists('file_managers');
    }
};
