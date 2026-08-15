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
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id()->unique();
            $table->integer('product_id')->index();
            $table->integer('from_shop_id')->index();
            $table->integer('to_shop_id')->index();
            $table->integer('qty')->default(0);
            $table->text('remark')->nullable();
            $table->integer('status')->default(1);
            $table->integer('request_by')->nullable()->index();
            $table->string('request_by_type')->nullable();
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
        Schema::dropIfExists('stock_transfers');
    }
};
