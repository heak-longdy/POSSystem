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
        if (!Schema::hasTable('other_incomes')) {
            Schema::create('other_incomes', function (Blueprint $table) {
                $table->id();
                $table->string('income_number', 50)->unique()->comment('e.g. INC-202610-0001');
                $table->unsignedBigInteger('category_id')->comment('FK to other_income_categories');
                $table->unsignedBigInteger('supplier_id')->nullable()->comment('FK to suppliers');
                $table->unsignedBigInteger('shop_id')->nullable()->comment('FK to shops');
                $table->string('title', 255)->comment('Brief summary or promotion name');
                $table->date('income_date')->comment('Accounting recognition date');

                // Calculation & Auditing metadata
                $table->date('calculation_period_start')->nullable();
                $table->date('calculation_period_end')->nullable();
                $table->string('calculation_quarter', 20)->nullable()->comment('e.g. 2026-Q3');
                $table->decimal('base_purchase_amount', 15, 2)->default(0.00);
                $table->decimal('bonus_rate_percent', 5, 2)->nullable();
                $table->text('calculation_notes')->nullable();

                // Financial balance tracking
                $table->decimal('expected_amount', 15, 2)->default(0.00);
                $table->decimal('received_amount', 15, 2)->default(0.00);
                $table->decimal('remaining_amount', 15, 2)->default(0.00);
                $table->string('payment_status', 20)->default('pending')->comment('pending, partial, paid, cancelled');

                // External Reference & Documentation
                $table->string('reference_no', 100)->nullable()->comment('Supplier letter/invoice ref');
                $table->string('attachment_path', 500)->nullable()->comment('Relative path to receipt/contract');
                $table->string('attachment_name', 255)->nullable()->comment('Original filename');
                $table->text('description')->nullable()->comment('Detailed remarks');

                $table->unsignedBigInteger('created_by')->comment('FK to users');
                $table->tinyInteger('status')->default(1)->comment('1: Active, 2: Disabled');
                $table->timestamps();
                $table->softDeletes();

                // Foreign Keys
                if (Schema::hasTable('other_income_categories')) {
                    $table->foreign('category_id')->references('id')->on('other_income_categories')->onDelete('restrict');
                }
                if (Schema::hasTable('suppliers')) {
                    $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
                }
                if (Schema::hasTable('shops')) {
                    $table->foreign('shop_id')->references('id')->on('shops')->onDelete('set null');
                }
                if (Schema::hasTable('users')) {
                    $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
                }

                // Performance Composite Indexes
                $table->index(['income_date', 'status']);
                $table->index(['supplier_id', 'income_date']);
                $table->index(['category_id', 'income_date']);
                $table->index(['shop_id', 'income_date']);
                $table->index(['payment_status', 'income_date']);
                $table->index('income_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('other_incomes');
    }
};
