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
        Schema::create('customer_paid', function (Blueprint $table) {
            $table->id(); // Auto-incrementing ID
            $table->string('customer_id');
            $table->string('customer_name');
            $table->string('currency')->nullable();
            $table->decimal('amount_usd', 10, 2)->nullable();
            $table->decimal('amount_kh', 10, 2)->nullable();
            $table->text('des')->nullable();
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
    public function down()
    {
        Schema::dropIfExists('customer_paid');
    }
};
