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
        if (!Schema::hasTable('suppliers')) {
            return;
        }

        Schema::table('suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('suppliers', 'code')) {
                $table->string('code', 50)->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('suppliers', 'contact_person')) {
                $table->string('contact_person', 100)->nullable()->after('name');
            }
            if (!Schema::hasColumn('suppliers', 'phone')) {
                $table->string('phone', 50)->nullable()->after('contact_person');
            }
            if (!Schema::hasColumn('suppliers', 'email')) {
                $table->string('email', 100)->nullable()->after('phone');
            }
            if (!Schema::hasColumn('suppliers', 'address')) {
                $table->text('address')->nullable()->after('email');
            }
            if (!Schema::hasColumn('suppliers', 'tax_number')) {
                $table->string('tax_number', 50)->nullable()->after('address');
            }
            if (!Schema::hasColumn('suppliers', 'bank_name')) {
                $table->string('bank_name', 100)->nullable()->after('tax_number');
            }
            if (!Schema::hasColumn('suppliers', 'bank_account_name')) {
                $table->string('bank_account_name', 100)->nullable()->after('bank_name');
            }
            if (!Schema::hasColumn('suppliers', 'bank_account_number')) {
                $table->string('bank_account_number', 100)->nullable()->after('bank_account_name');
            }
            if (!Schema::hasColumn('suppliers', 'notes')) {
                $table->text('notes')->nullable()->after('bank_account_number');
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
        if (!Schema::hasTable('suppliers')) {
            return;
        }

        Schema::table('suppliers', function (Blueprint $table) {
            $columns = [
                'code',
                'contact_person',
                'phone',
                'email',
                'address',
                'tax_number',
                'bank_name',
                'bank_account_name',
                'bank_account_number',
                'notes',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('suppliers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
