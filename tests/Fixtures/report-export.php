<?php
// Render actual report scripts without a database or the authenticated layout.
chdir(dirname(__DIR__, 2));
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
$app->setLocale('en');
$app->instance('request', Illuminate\Http\Request::create('http://localhost/admin/report/sales/daily'));
foreach (['sales' => 'sales', 'orderTransaction' => 'order-transaction', 'inventoryMovement' => 'inventory-movement', 'staffExpense' => 'staff-expense'] as $view => $module) {
    $source = file_get_contents('resources/admin/views/pages/report/' . $view . '/index.blade.php');
    $source = explode("@section('script')", $source, 2)[1];
    $source = preg_replace('/^@stop\s*$/m', '', $source);
    file_put_contents($argv[1] . '/' . $view . '.html', Illuminate\Support\Facades\Blade::render($source, [
        'routeName' => 'report-' . $module, 'viewMode' => 'daily', 'grouping' => 'ungrouped',
    ]));
}
file_put_contents($argv[1] . '/messages.json', json_encode(trans('report_export')));
