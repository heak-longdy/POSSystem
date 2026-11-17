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
        if (!Schema::hasTable('purchases')) {
            Schema::create('purchases', function (Blueprint $table) {
                $table->id();
                $table->string('purchase_number', 50)->unique();
                $table->string('supplier_invoice_no', 100)->nullable();
                $table->unsignedBigInteger('supplier_id');
                $table->unsignedBigInteger('shop_id');
                $table->date('purchase_date');
                $table->date('delivery_date')->nullable();
                $table->string('status', 30)->default('draft');
                $table->string('payment_status', 30)->default('unpaid');
                $table->date('payment_due_date')->nullable();

                // Financials
                $table->decimal('subtotal', 15, 2)->default(0.00);
                $table->string('discount_type', 20)->default('fixed');
                $table->decimal('discount_rate', 10, 2)->default(0.00);
                $table->decimal('discount_amount', 15, 2)->default(0.00);
                $table->decimal('tax_rate', 5, 2)->default(0.00);
                $table->decimal('tax_amount', 15, 2)->default(0.00);
                $table->decimal('shipping_cost', 15, 2)->default(0.00);
                $table->decimal('total_amount', 15, 2)->default(0.00);
                $table->decimal('paid_amount', 15, 2)->default(0.00);

                // Audit & Concurrency
                $table->string('creation_request_key', 100)->nullable();
                $table->text('remark')->nullable();
                $table->unsignedBigInteger('created_by');
                $table->unsignedBigInteger('received_by')->nullable();
                $table->dateTime('received_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                // Foreign Keys
                if (Schema::hasTable('suppliers')) {
                    $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('restrict');
                }
                if (Schema::hasTable('shops')) {
                    $table->foreign('shop_id')->references('id')->on('shops')->onDelete('restrict');
                }
                if (Schema::hasTable('users')) {
                    $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
                    $table->foreign('received_by')->references('id')->on('users')->onDelete('set null');
                }

                // Indexes
                $table->index(['supplier_id', 'purchase_date']);
                $table->index(['shop_id', 'purchase_date']);
                $table->index(['status', 'purchase_date']);
                $table->index(['payment_status', 'purchase_date']);
                $table->index('purchase_date');
            });
        }

        if (!Schema::hasTable('purchase_items')) {
            Schema::create('purchase_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('purchase_id');
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('uom_id')->nullable();
                $table->decimal('ordered_qty', 10, 2)->default(1.00);
                $table->decimal('received_qty', 10, 2)->default(0.00);
                $table->decimal('unit_cost', 15, 2)->default(0.00);
                $table->decimal('discount_amount', 15, 2)->default(0.00);
                $table->decimal('tax_amount', 15, 2)->default(0.00);
                $table->decimal('subtotal', 15, 2)->default(0.00);
                $table->boolean('update_product_cost')->default(true);
                $table->string('remark', 255)->nullable();
                $table->timestamps();
                $table->softDeletes();

                if (Schema::hasTable('purchases')) {
                    $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('cascade');
                }
                if (Schema::hasTable('products')) {
                    $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict');
                }

                $table->index('purchase_id');
                $table->index('product_id');
            });
        }

        if (!Schema::hasTable('purchase_payments')) {
            Schema::create('purchase_payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('purchase_id');
                $table->unsignedBigInteger('supplier_id');
                $table->string('payment_number', 50)->unique();
                $table->decimal('amount', 15, 2);
                $table->date('payment_date');
                $table->string('payment_method', 50)->default('Cash');
                $table->string('reference_no', 100)->nullable();
                $table->string('receipt_path', 500)->nullable();
                $table->string('receipt_name', 255)->nullable();
                $table->text('note')->nullable();
                $table->unsignedBigInteger('created_by');
                $table->timestamps();
                $table->softDeletes();

                if (Schema::hasTable('purchases')) {
                    $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('cascade');
                }
                if (Schema::hasTable('suppliers')) {
                    $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('restrict');
                }
                if (Schema::hasTable('users')) {
                    $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
                }

                $table->index(['purchase_id', 'payment_date']);
                $table->index(['supplier_id', 'payment_date']);
                $table->index('payment_date');
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
        Schema::dropIfExists('purchase_payments');
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
    }
};
