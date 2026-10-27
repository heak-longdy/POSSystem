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
        if (!Schema::hasTable('other_income_categories')) {
            Schema::create('other_income_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('code', 50)->unique();
                $table->boolean('requires_supplier')->default(false);
                $table->string('description', 255)->nullable();
                $table->integer('ordering')->default(0);
                $table->tinyInteger('status')->default(1)->comment('1: Active, 2: Disabled');
                $table->boolean('is_system')->default(false);
                $table->timestamps();
                $table->softDeletes();

                $table->index('status');
                $table->index('ordering');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('other_income_categories');
    }
};
