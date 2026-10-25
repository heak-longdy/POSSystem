<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddDataAccessToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'data_access')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('data_access', 20)->default('own')->after('role');
            });

            // Grant existing administrative accounts full store-wide access by default
            DB::table('users')->whereIn('role', ['super_admin', 'admin'])->update(['data_access' => 'all']);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'data_access')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('data_access');
            });
        }
    }
}
