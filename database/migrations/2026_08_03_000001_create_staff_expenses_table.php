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
        if (!Schema::hasTable('staff_expenses')) {
            Schema::create('staff_expenses', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('staff_id')->comment('FK to staffs table');
                $table->unsignedBigInteger('shop_id')->nullable()->comment('FK to shops table');
                $table->string('type', 30)->comment('Salary, Bonus, Deduction, Other');
                $table->decimal('amount', 15, 2)->comment('Expense amount in USD');
                $table->date('expense_date')->comment('Date of transaction');
                $table->string('description', 1000)->nullable()->comment('Notes/Remarks');
                $table->unsignedBigInteger('created_by')->comment('FK to users table');
                $table->tinyInteger('status')->default(1)->comment('1: Active, 2: Disabled');
                $table->softDeletes();
                $table->timestamps();

                // Foreign key constraints
            if (Schema::hasTable('staffs')) {
                $table->foreign('staff_id')->references('id')->on('staffs')->onDelete('cascade');
            }
            if (Schema::hasTable('shops')) {
                $table->foreign('shop_id')->references('id')->on('shops')->onDelete('set null');
            }
            if (Schema::hasTable('users')) {
                $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            }

                // Performance Composite Indexes
                $table->index(['staff_id', 'expense_date']);
                $table->index(['shop_id', 'expense_date']);
                $table->index(['type', 'expense_date']);
                $table->index('expense_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_expenses');
    }
};
