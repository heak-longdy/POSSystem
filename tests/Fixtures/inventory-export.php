<?php
// Render the actual inventory page scripts without accessing application data.
chdir(dirname(__DIR__, 2));
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
$app->setLocale('en');
$app->instance('request', Illuminate\Http\Request::create('http://localhost/admin/stock-transfer/list/1?search=Saved&shop_id=2'));
foreach (['stockIn', 'stockOut', 'stockTransfer', 'stockOnHand', 'stockMovement'] as $module) {
    $routeName = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $module));
    $source = file_get_contents('resources/admin/views/pages/inventoryManagement/' . $module . '/index.blade.php');
    $source = explode("@section('script')", $source, 2)[1];
    $source = preg_replace('/^@stop\s*$/m', '', $source);
    file_put_contents($argv[1] . '/' . $module . '.html', Illuminate\Support\Facades\Blade::render($source, [
        'routeName' => $routeName, 'status' => '1', 'shop' => null,
    ]));
}
file_put_contents($argv[1] . '/missing-route.js', view('admin::pages.inventoryManagement.partials.excel-request', ['routeName' => 'missing-inventory-module'])->render());
file_put_contents($argv[1] . '/messages.json', json_encode(trans('inventory_export')));
