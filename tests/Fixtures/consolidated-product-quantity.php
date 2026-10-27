<?php
// Render the real report against fixture data, without touching the application database.
chdir(dirname(__DIR__, 2));
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'session.driver' => 'array', 'cache.default' => 'array']);
app(Spatie\Permission\PermissionRegistrar::class)->initializeCache();
require_once database_path('migrations/2026_08_02_231335_create_permission_tables.php');
(new CreatePermissionTables())->up();
$app->instance('request', Illuminate\Http\Request::create('http://localhost/admin/report/consolidated-product-quantity'));
Illuminate\Support\Facades\Gate::before(fn () => true);
auth()->setUser(new App\Models\User(['role' => 'super_admin', 'name' => 'Test admin']));
$source = file_get_contents('resources/admin/views/pages/report/consolidatedProductQuantity/index.blade.php');
$source = preg_replace('/^@(extends|section|stop).*$/m', '', $source);
$source = preg_replace("/^\s*@include\('admin::shared.header'.*$/m", '', $source);
$rows = collect([(object) ['product_id' => 1, 'product_code' => 'P001', 'product_name' => 'Tea & ទឹកតែ',
    'category_name' => 'Drinks', 'uom_name' => 'Box', 'order_count' => 2, 'total_quantity' => 12]]);
$summary = (object) ['product_count' => 1, 'order_count' => 2, 'shop_count' => 2, 'total_quantity' => 12];
foreach (['en', 'km'] as $locale) {
    $app->setLocale($locale);
    $data = [
        'routeName' => 'report-consolidated-product-quantity',
        'filters' => ['from_date' => '2026-10-01', 'to_date' => '2026-10-27', 'search' => ''],
        'rows' => new Illuminate\Pagination\LengthAwarePaginator($rows, 1, 50),
        'summary' => $summary,
        'shops' => collect([(object) ['id' => 1, 'name' => 'Central'], (object) ['id' => 2, 'name' => 'Branch']]),
        'categories' => collect([(object) ['id' => 1, 'name' => 'Drinks']]),
        'products' => collect([(object) ['id' => 1, 'name' => 'Tea & ទឹកតែ']]),
        'errors' => new Illuminate\Support\ViewErrorBag(),
    ];
    file_put_contents($argv[1] . '/' . $locale . '.html', Illuminate\Support\Facades\Blade::render($source, $data));
}
file_put_contents($argv[1] . '/response.json', json_encode(['rows' => $rows, 'summary' => $summary]));
