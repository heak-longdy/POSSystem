<?php
chdir(dirname(__DIR__, 2));
$output = $argv[1];
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'session.driver' => 'array']);
Illuminate\Support\Facades\Gate::before(fn () => false);
$app->instance('request', Illuminate\Http\Request::create('http://localhost/admin/stock-transfer/create'));
$compiler = new Illuminate\View\Compilers\BladeCompiler(new Illuminate\Filesystem\Filesystem, sys_get_temp_dir());
$render = function($template, $vars) use ($compiler) {
    extract($vars);
    ob_start();
    eval('?>' . $compiler->compileString($template));
    return ob_get_clean();
};
$vars = ['readonly' => false, 'data' => null, 'id' => null, 'routeName' => 'stock-transfer',
    'shop' => null, 'status' => 1, 'viewMode' => 'summary', 'grouping' => 'day', 'filters' => []];
foreach (['inventoryManagement/stockTransfer/create','inventoryManagement/stockOut/create','product/store','staff/store','staffExpense/store','customer/store','setting/invoice',
    'inventoryManagement/stockIn/index', 'inventoryManagement/stockOut/index',
    'inventoryManagement/stockTransfer/index', 'inventoryManagement/stockMovement/index',
    'inventoryManagement/stockOnHand/index', 'report/sales/index', 'report/inventoryMovement/index',
    'report/orderTransaction/index', 'report/staffExpense/index', 'report/customerRemaining/index'] as $page) {
    $s = file_get_contents('resources/admin/views/pages/'.$page.'.blade.php');
    preg_match_all('/<script\b[^>]*>(.*?)<\/script>/s', $s, $scripts);
    $pageVars = $vars;
    if ($page === 'inventoryManagement/stockTransfer/index') {
        $pageVars['shop'] = (object) ['id' => 11, 'name' => 'Existing selection'];
    }
    $code = $render(implode("\n", $scripts[1]), $pageVars);
    file_put_contents($output.'/'.str_replace('/', '-', $page).'.js', $code);
    if ($page === 'inventoryManagement/stockTransfer/create') {
        preg_match_all('/<div class="select2Group">.*?<\/select>(?:\s*<div class="select2Reset".*?<\/div>)?\s*<\/div>/s', $s, $groups);
        $selected = (object) ['id' => 11, 'name' => 'Existing selection'];
        foreach (['selected', 'empty', 'readonly'] as $state) {
            $fieldVars = array_merge($vars, [
                'readonly' => $state === 'readonly',
                'selectedShop' => $state === 'empty' ? null : $selected,
                'selectedProduct' => $state === 'empty' ? null : $selected,
                'selectedToShop' => $state === 'empty' ? null : $selected,
            ]);
            file_put_contents($output.'/stock-transfer-'.$state.'.html', $render(implode("\n", $groups[0]), $fieldVars));
        }
    }
    if ($page === 'inventoryManagement/stockTransfer/index') {
        $filter = file_get_contents('resources/admin/views/pages/inventoryManagement/stockTransfer/filter.blade.php');
        file_put_contents($output.'/stock-transfer-filter.html', $render($filter, array_merge($vars, [
            'shop' => (object) ['id' => 11, 'name' => 'Existing selection'],
        ])));
        preg_match_all('/<style>(.*?)<\/style>/s', $s, $styles);
        file_put_contents($output.'/stock-transfer-filter.css', implode("\n", $styles[1]));
    }
}
echo "Rendered real page scripts without database access.\n";
