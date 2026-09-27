<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('invoice_settings')) {
            Schema::create('invoice_settings', function (Blueprint $table) {
                $table->id();
                $table->string('prefix', 20)->default('NO');
                $table->string('separator', 5)->default('-');
                $table->string('date_format', 20)->default('none'); // 'none', 'Y', 'Ym', 'Ymd'
                $table->unsignedInteger('digit_length')->default(4);
                $table->unsignedBigInteger('start_number')->default(1);
                $table->string('reset_cycle', 20)->default('never'); // 'never', 'yearly', 'monthly', 'daily'
                $table->timestamps();
            });

            DB::table('invoice_settings')->insert([
                'prefix'       => 'NO',
                'separator'    => '-',
                'date_format'  => 'none',
                'digit_length' => 4,
                'start_number' => 1,
                'reset_cycle'  => 'never',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        if (!Schema::hasTable('invoice_sequences')) {
            Schema::create('invoice_sequences', function (Blueprint $table) {
                $table->id();
                $table->string('prefix_key', 50)->unique();
                $table->unsignedBigInteger('last_number')->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_sequences');
        Schema::dropIfExists('invoice_settings');
    }
};
