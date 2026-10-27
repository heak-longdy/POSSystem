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
        if (Schema::hasTable('other_incomes')) {
            Schema::table('other_incomes', function (Blueprint $table) {
                $columnsToDrop = [];

                if (Schema::hasColumn('other_incomes', 'expected_amount')) {
                    $columnsToDrop[] = 'expected_amount';
                }
                if (Schema::hasColumn('other_incomes', 'remaining_amount')) {
                    $columnsToDrop[] = 'remaining_amount';
                }

                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('other_incomes')) {
            Schema::table('other_incomes', function (Blueprint $table) {
                if (!Schema::hasColumn('other_incomes', 'expected_amount')) {
                    $table->decimal('expected_amount', 15, 2)->default(0.00)->after('income_date');
                }
                if (!Schema::hasColumn('other_incomes', 'remaining_amount')) {
                    $table->decimal('remaining_amount', 15, 2)->default(0.00)->after('received_amount');
                }
            });
        }
    }
};
