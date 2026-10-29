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
        if (! Schema::hasTable('customers')) {
            return;
        }

        if (! Schema::hasColumn('customers', 'code')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('code')->nullable()->unique()->after('id');
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
        if (! Schema::hasTable('customers')) {
            return;
        }

        if (Schema::hasColumn('customers', 'code')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('code');
            });
        }
    }
};

