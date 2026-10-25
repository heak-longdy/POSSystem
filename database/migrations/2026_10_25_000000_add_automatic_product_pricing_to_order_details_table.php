<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->decimal('original_price', 15, 2)->nullable();
            $table->decimal('unit_price', 15, 2)->nullable();
            $table->string('pricing_method', 32)->nullable();
        });
    }

    public function down()
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn(['original_price', 'unit_price', 'pricing_method']);
        });
    }
};
