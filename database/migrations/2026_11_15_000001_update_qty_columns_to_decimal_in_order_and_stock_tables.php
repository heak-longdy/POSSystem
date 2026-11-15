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
        if (Schema::hasTable('order_details') && Schema::hasColumn('order_details', 'qty')) {
            Schema::table('order_details', function (Blueprint $table) {
                $table->decimal('qty', 10, 2)->default(1)->change();
            });
        }

        if (Schema::hasTable('stock_outs') && Schema::hasColumn('stock_outs', 'qty')) {
            Schema::table('stock_outs', function (Blueprint $table) {
                $table->decimal('qty', 10, 2)->default(0)->change();
            });
        }

        if (Schema::hasTable('stock_on_hands') && Schema::hasColumn('stock_on_hands', 'current_stock')) {
            Schema::table('stock_on_hands', function (Blueprint $table) {
                $table->decimal('current_stock', 10, 2)->default(0)->change();
            });
        }

        if (Schema::hasTable('stock_histories')) {
            Schema::table('stock_histories', function (Blueprint $table) {
                if (Schema::hasColumn('stock_histories', 'qty')) {
                    $table->decimal('qty', 10, 2)->default(0)->change();
                }
                if (Schema::hasColumn('stock_histories', 'stock_out')) {
                    $table->decimal('stock_out', 10, 2)->default(0)->change();
                }
                if (Schema::hasColumn('stock_histories', 'current_stock')) {
                    $table->decimal('current_stock', 10, 2)->default(0)->change();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('order_details') && Schema::hasColumn('order_details', 'qty')) {
            Schema::table('order_details', function (Blueprint $table) {
                $table->integer('qty')->default(1)->change();
            });
        }

        if (Schema::hasTable('stock_outs') && Schema::hasColumn('stock_outs', 'qty')) {
            Schema::table('stock_outs', function (Blueprint $table) {
                $table->integer('qty')->default(0)->change();
            });
        }

        if (Schema::hasTable('stock_on_hands') && Schema::hasColumn('stock_on_hands', 'current_stock')) {
            Schema::table('stock_on_hands', function (Blueprint $table) {
                $table->integer('current_stock')->default(0)->change();
            });
        }

        if (Schema::hasTable('stock_histories')) {
            Schema::table('stock_histories', function (Blueprint $table) {
                if (Schema::hasColumn('stock_histories', 'qty')) {
                    $table->integer('qty')->default(0)->change();
                }
                if (Schema::hasColumn('stock_histories', 'stock_out')) {
                    $table->integer('stock_out')->default(0)->change();
                }
                if (Schema::hasColumn('stock_histories', 'current_stock')) {
                    $table->integer('current_stock')->default(0)->change();
                }
            });
        }
    }
};
