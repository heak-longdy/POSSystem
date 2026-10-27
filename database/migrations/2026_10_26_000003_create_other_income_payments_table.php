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
        if (!Schema::hasTable('other_income_payments')) {
            Schema::create('other_income_payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('other_income_id');
                $table->string('payment_number', 50)->unique()->comment('e.g. PAY-INC-202610-0001');
                $table->decimal('amount', 15, 2);
                $table->date('payment_date');
                $table->string('payment_method', 50)->default('Cash')->comment('Cash, Bank Transfer, ABA, Cheque, Credit Note, Other');
                $table->string('reference_no', 100)->nullable()->comment('Bank txn ID / slip #');
                $table->string('receipt_path', 500)->nullable()->comment('Bank slip attachment path');
                $table->string('receipt_name', 255)->nullable()->comment('Original slip filename');
                $table->text('note')->nullable();
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
                $table->softDeletes();

                if (Schema::hasTable('other_incomes')) {
                    $table->foreign('other_income_id')->references('id')->on('other_incomes')->onDelete('cascade');
                }
                if (Schema::hasTable('users')) {
                    $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
                }

                $table->index(['other_income_id', 'payment_date']);
                $table->index('payment_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('other_income_payments');
    }
};
