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
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id(); // Auto-incrementing ID
            $table->string('name')->nullable();
            $table->integer('ordering')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('profile')->nullable();
            $table->string('password')->nullable();
            $table->integer('total_point')->default(0);
            $table->string('user');
            $table->integer('status')->default(1);
            $table->timestamps(); // created_at and updated_at
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
