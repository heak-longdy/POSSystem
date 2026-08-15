<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->double('paid_amount')->default(0)->after('payment_status');
        });

        DB::table('bookings')
            ->where('payment_status', 'Paid')
            ->update(['paid_amount' => DB::raw('total_price')]);
    }

    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('paid_amount');
        });
    }
};
