<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('booking_payments', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->default('Cash')->after('amount');
            $table->dateTime('payment_date')->nullable()->after('payment_method');
        });

        DB::table('booking_payments')
            ->whereNull('payment_date')
            ->update([
                'payment_date' => DB::raw('created_at'),
            ]);
    }

    public function down()
    {
        Schema::table('booking_payments', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_date']);
        });
    }
};
