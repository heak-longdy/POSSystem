<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLanguagePreferenceToUsersTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'language_preference')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('language_preference', 5)->default('en')->after('status');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('users', 'language_preference')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('language_preference');
            });
        }
    }
}
