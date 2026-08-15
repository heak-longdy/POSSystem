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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id()->unique();
            $table->integer('customer_id')->nullable()->index();
            $table->integer('barber_id')->nullable()->index();
            $table->integer('shop_id')->nullable()->index();
            $table->dateTime('booking_date')->nullable()->index();
            $table->double('total_price')->default(0);
            $table->double('total_commission')->default(0);
            $table->double('total_discount')->default(0);
            $table->integer('total_point')->default(0);
            $table->string('payment_status')->default('Pending')->index();
            $table->dateTime('payment_date')->nullable();
            $table->string('invoice_number')->nullable()->unique();
            $table->string('pay_way')->nullable();
            $table->text('remark')->nullable();
            $table->timestamps();
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
        Schema::dropIfExists('bookings');
    }
};
