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
        Schema::create('job_lists', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('number_of_day');
            $table->date('post_date');
            $table->date('close_date')->nullable();
            $table->string('user');
            $table->unsignedBigInteger('placement_type_id');
            $table->unsignedBigInteger('position_id');
            $table->unsignedBigInteger('sector_id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('country_id');
            $table->unsignedBigInteger('job_category_id');
            $table->unsignedBigInteger('location_id');
            $table->string('title');
            $table->enum('term', ['Full-time', 'Part-time', 'Contract']);
            $table->decimal('salary', 10, 2)->nullable();
            $table->integer('year_experience')->nullable();
            $table->integer('number_of_hire')->nullable();
            $table->enum('status', ['open', 'closed', 'pending']);
            $table->string('image')->nullable();
            $table->enum('sex', ['Male', 'Female', 'Other'])->nullable();
            $table->boolean('is_premium')->default(false);
            $table->string('job_level')->nullable();
            $table->text('job_des')->nullable();
            $table->text('job_requirement')->nullable();
            $table->text('job_res')->nullable();
            $table->enum('job_type', ['Permanent', 'Temporary', 'Internship']);
            $table->string('hr_name')->nullable();
            $table->string('hr_phone_number')->nullable();
            $table->string('hr_email')->nullable();
            $table->decimal('salary_from', 10, 2)->nullable();
            $table->decimal('salary_to', 10, 2)->nullable();
            $table->softDeletes(); // This will create the deleted_at column
            $table->boolean('is_negotiate')->default(false);
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
        Schema::dropIfExists('job_lists');
    }
};
