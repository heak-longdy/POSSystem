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
        // 1. Create purchases table (Purchase Order Header)
        if (!Schema::hasTable('purchases')) {
            Schema::create('purchases', function (Blueprint $table) {
                $table->id();
                $table->string('purchase_number', 50)->unique()->index()->comment('e.g. PO-202611-0001');
                $table->unsignedBigInteger('supplier_id')->nullable()->index();
                $table->unsignedBigInteger('shop_id')->index();
                $table->date('order_date')->index();
                $table->date('expected_delivery_date')->nullable();
                $table->string('order_status', 30)->default('ordered')->index()->comment('draft, ordered, partial_received, received, cancelled');
                
                // Financials
                $table->decimal('subtotal', 15, 2)->default(0.00);
                $table->decimal('discount_amount', 15, 2)->default(0.00);
                $table->decimal('tax_amount', 15, 2)->default(0.00);
                $table->decimal('shipping_cost', 15, 2)->default(0.00);
                $table->decimal('total_amount', 15, 2)->default(0.00);
                $table->decimal('paid_amount', 15, 2)->default(0.00);
                $table->string('payment_status', 20)->default('unpaid')->index()->comment('unpaid, partial, paid');
                $table->string('payment_method', 50)->nullable()->comment('Default or initial payment method');
                $table->string('reference_no', 100)->nullable()->comment('Supplier invoice / quotation reference');
                $table->text('remark')->nullable();
                
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->tinyInteger('status')->default(1)->comment('1: Active, 2: Disabled');
                $table->timestamps();
                $table->softDeletes();

                if (Schema::hasTable('suppliers')) {
                    $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
                }
                if (Schema::hasTable('shops')) {
                    $table->foreign('shop_id')->references('id')->on('shops')->onDelete('restrict');
                }
                if (Schema::hasTable('users')) {
                    $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                }
            });
        }

        // 2. Create purchase_items table (Purchase Order Line Items)
        if (!Schema::hasTable('purchase_items')) {
            Schema::create('purchase_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('purchase_id')->index();
                $table->unsignedBigInteger('product_id')->index();
                $table->decimal('unit_cost', 15, 2)->default(0.00);
                $table->decimal('qty', 10, 2)->default(0.00)->comment('Quantity ordered');
                $table->decimal('qty_received', 10, 2)->default(0.00)->comment('Accumulated quantity received');
                $table->decimal('subtotal', 15, 2)->default(0.00)->comment('qty * unit_cost');
                $table->string('remark', 255)->nullable();
                $table->timestamps();
                $table->softDeletes();

                if (Schema::hasTable('purchases')) {
                    $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('cascade');
                }
                if (Schema::hasTable('products')) {
                    $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict');
                }
            });
        }

        // 3. Create purchase_receipts table (Goods Receiving Batches)
        if (!Schema::hasTable('purchase_receipts')) {
            Schema::create('purchase_receipts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('purchase_id')->index();
                $table->string('receipt_number', 50)->unique()->index()->comment('e.g. REC-202611-0001');
                $table->date('received_date')->index();
                $table->unsignedBigInteger('received_by')->nullable()->index();
                $table->string('reference_no', 100)->nullable()->comment('Delivery order / bill of lading');
                $table->text('remark')->nullable();
                $table->timestamps();
                $table->softDeletes();

                if (Schema::hasTable('purchases')) {
                    $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('cascade');
                }
                if (Schema::hasTable('users')) {
                    $table->foreign('received_by')->references('id')->on('users')->onDelete('set null');
                }
            });
        }

        // 4. Create purchase_receipt_items table (Item Quantities per Receipt Batch)
        if (!Schema::hasTable('purchase_receipt_items')) {
            Schema::create('purchase_receipt_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('purchase_receipt_id')->index();
                $table->unsignedBigInteger('purchase_item_id')->index();
                $table->unsignedBigInteger('product_id')->index();
                $table->decimal('qty_received', 10, 2)->default(0.00);
                $table->unsignedBigInteger('stock_history_id')->nullable()->index();
                $table->timestamps();

                if (Schema::hasTable('purchase_receipts')) {
                    $table->foreign('purchase_receipt_id')->references('id')->on('purchase_receipts')->onDelete('cascade');
                }
                if (Schema::hasTable('purchase_items')) {
                    $table->foreign('purchase_item_id')->references('id')->on('purchase_items')->onDelete('cascade');
                }
                if (Schema::hasTable('products')) {
                    $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict');
                }
            });
        }

        // 5. Create purchase_payments table (Payments Ledger)
        if (!Schema::hasTable('purchase_payments')) {
            Schema::create('purchase_payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('purchase_id')->index();
                $table->string('payment_number', 50)->unique()->index()->comment('e.g. PAY-PO-202611-0001');
                $table->decimal('amount', 15, 2)->default(0.00);
                $table->date('payment_date')->index();
                $table->string('payment_method', 50)->default('cash')->comment('cash, bank_transfer, aba, cheque, other');
                $table->string('reference_no', 100)->nullable()->comment('Bank txn ID / check number');
                $table->string('receipt_path', 500)->nullable()->comment('Uploaded slip attachment path');
                $table->string('receipt_name', 255)->nullable()->comment('Original filename');
                $table->text('note')->nullable();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->timestamps();
                $table->softDeletes();

                if (Schema::hasTable('purchases')) {
                    $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('cascade');
                }
                if (Schema::hasTable('users')) {
                    $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                }
            });
        }

        // 6. Add purchase_id & purchase_receipt_id to stock_ins for traceability
        if (Schema::hasTable('stock_ins')) {
            Schema::table('stock_ins', function (Blueprint $table) {
                if (!Schema::hasColumn('stock_ins', 'purchase_id')) {
                    $table->unsignedBigInteger('purchase_id')->nullable()->after('supplier_id')->index();
                }
                if (!Schema::hasColumn('stock_ins', 'purchase_receipt_id')) {
                    $table->unsignedBigInteger('purchase_receipt_id')->nullable()->after('purchase_id')->index();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('stock_ins')) {
            Schema::table('stock_ins', function (Blueprint $table) {
                if (Schema::hasColumn('stock_ins', 'purchase_receipt_id')) {
                    $table->dropColumn('purchase_receipt_id');
                }
                if (Schema::hasColumn('stock_ins', 'purchase_id')) {
                    $table->dropColumn('purchase_id');
                }
            });
        }

        Schema::dropIfExists('purchase_payments');
        Schema::dropIfExists('purchase_receipt_items');
        Schema::dropIfExists('purchase_receipts');
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
    }
};
