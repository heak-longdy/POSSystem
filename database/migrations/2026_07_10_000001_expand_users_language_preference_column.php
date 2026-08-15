<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExpandUsersLanguagePreferenceColumn extends Migration
{
    public function up()
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'language_preference')) {
            DB::statement("ALTER TABLE users MODIFY language_preference VARCHAR(20) NOT NULL DEFAULT 'en'");
        }
    }

    public function down()
    {
        if (Schema::hasColumn('users', 'language_preference')) {
            DB::statement("ALTER TABLE users MODIFY language_preference VARCHAR(5) NOT NULL DEFAULT 'en'");
        }
    }
}
