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
        if (!Schema::hasTable('customers')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'image_1')) {
                $table->string('image_1')->nullable()->after('profile');
            }
            if (!Schema::hasColumn('customers', 'image_2')) {
                $table->string('image_2')->nullable()->after('image_1');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('customers')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'image_2')) {
                $table->dropColumn('image_2');
            }
            if (Schema::hasColumn('customers', 'image_1')) {
                $table->dropColumn('image_1');
            }
        });
    }
};
