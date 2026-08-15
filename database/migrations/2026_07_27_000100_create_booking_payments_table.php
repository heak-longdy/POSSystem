<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('booking_payments', function (Blueprint $table) {
            $table->id();
            $table->integer('booking_id')->index();
            $table->double('amount')->default(0);
            $table->text('note')->nullable();
            $table->integer('created_by')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('bookings')
            ->where('payment_status', 'Paid')
            ->where('total_price', '>', 0)
            ->orderBy('id')
            ->chunk(500, function ($bookings) {
                foreach ($bookings as $booking) {
                    DB::table('booking_payments')->insert([
                        'booking_id' => $booking->id,
                        'amount' => $booking->total_price,
                        'note' => 'Migrated paid booking balance.',
                        'created_by' => null,
                        'created_at' => $booking->payment_date ?: now(),
                        'updated_at' => $booking->payment_date ?: now(),
                    ]);
                }
            });
    }

    public function down()
    {
        Schema::dropIfExists('booking_payments');
    }
};
