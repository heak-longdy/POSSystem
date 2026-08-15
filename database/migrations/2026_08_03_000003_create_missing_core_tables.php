<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('folders')) {
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

        if (!Schema::hasTable('file_managers')) {
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

        if (!Schema::hasTable('pages')) {
            Schema::create('pages', function (Blueprint $table) {
                $table->increments('id');
                $table->json('title')->nullable();
                $table->json('content')->nullable();
                $table->string('type');
                $table->tinyInteger('status');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('fund_ranges')) {
            Schema::create('fund_ranges', function (Blueprint $table) {
                $table->id();
                $table->double('min_range');
                $table->double('max_range');
                $table->integer('ordering');
                $table->integer('status');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('rate_ranges')) {
            Schema::create('rate_ranges', function (Blueprint $table) {
                $table->id();
                $table->integer('fund_range_id');
                $table->double('min_rate');
                $table->double('max_rate');
                $table->integer('ordering');
                $table->integer('status');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rate_ranges');
        Schema::dropIfExists('fund_ranges');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('file_managers');
        Schema::dropIfExists('folders');
    }
};
