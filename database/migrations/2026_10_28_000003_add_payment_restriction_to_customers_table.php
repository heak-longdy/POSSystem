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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('payment_restriction', 1)
                ->default('N')
                ->comment('N = No (Unrestricted), Y = Yes (Full Payment Required)')
                ->after('status')
                ->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['payment_restriction']);
            $table->dropColumn('payment_restriction');
        });
    }
};

