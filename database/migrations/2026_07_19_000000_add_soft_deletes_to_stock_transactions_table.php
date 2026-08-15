<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('stock_ins', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('stock_outs', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('stock_ins', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('stock_outs', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
