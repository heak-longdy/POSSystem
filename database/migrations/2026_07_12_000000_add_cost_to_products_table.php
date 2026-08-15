<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        if (! Schema::hasTable('products')) {
            return;
        }

        $columns = Schema::getColumnListing('products');
        if (in_array('Cost', $columns, true) && ! in_array('cost', $columns, true)) {
            $this->renameCostColumn('Cost', 'cost');
            return;
        }

        if (! in_array('cost', $columns, true)) {
            Schema::table('products', function (Blueprint $table) {
                $table->double('cost')->nullable()->after('name');
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
        if (! Schema::hasTable('products')) {
            return;
        }

        $columns = Schema::getColumnListing('products');
        if (in_array('cost', $columns, true) && ! in_array('Cost', $columns, true)) {
            $this->renameCostColumn('cost', 'Cost');
        }
    }

    private function renameCostColumn(string $from, string $to): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement(sprintf('ALTER TABLE `products` CHANGE `%s` `%s` DOUBLE NULL', $from, $to));
            return;
        }

        Schema::table('products', function (Blueprint $table) use ($from, $to) {
            $table->renameColumn($from, $to);
        });
    }
};
