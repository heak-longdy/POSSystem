<?php
// Render real Dashboard views with isolated UI fixture data; never use the application database.
chdir(dirname(__DIR__, 2));
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'session.driver' => 'array', 'cache.default' => 'array']);
app(Spatie\Permission\PermissionRegistrar::class)->initializeCache();
require_once database_path('migrations/2026_08_02_231335_create_permission_tables.php');
(new CreatePermissionTables())->up();
$app->instance('request', Illuminate\Http\Request::create('http://localhost/admin/dashboard'));
Illuminate\Support\Facades\Gate::before(fn () => true);
auth()->setUser(new App\Models\User(['role' => 'super_admin', 'name' => 'Test admin']));
$source = file_get_contents('resources/admin/views/pages/dashboard.blade.php');
$source = preg_replace('/^@(extends|section|stop|endsection).*$/m', '', $source);
$source = preg_replace("/^\s*@include\('admin::shared.header'.*$/m", '', $source);
// Browser fixtures stay offline; the live Dashboard's unused chart CDN is unrelated to this filter.
$source = preg_replace('/^\s*<(link|script src).*$/m', '', $source);
$product = [
    'product_id' => 1, 'name' => 'Test product', 'category' => 'Drinks', 'progress' => 98.5,
    'priority' => 'very_high', 'budget' => '$450.00',
];
$order = (new App\Models\Order())->forceFill([
    'id' => 1, 'invoice_number' => 'OCT-001', 'total_price' => 125, 'paid_amount' => 25,
    'payment_status' => 'Partial', 'order_date' => '2026-10-15', 'created_at' => '2026-10-15 12:30:00',
]);
$order->setRelation('customer', new App\Models\Customer(['name' => 'Test customer']));
$order->setRelation('shop', null);
$base = [
    'hasFullDataAccess' => true, 'totalOrderPrice' => 450, 'pendingOrderCount' => 3,
    'totalOrderRemainingAmount' => 200, 'remainingOrderCount' => 4,
    'totalProductCount' => 10, 'activeProductCount' => 9, 'totalCustomerCount' => 6, 'activeCustomerCount' => 5,
    'totalStaffCount' => 4, 'activeStaffCount' => 3, 'totalUserCount' => 3, 'activeUserCount' => 2,
    'recentTransactions' => collect([$order]), 'productPerformance' => collect([$product]),
    'productPerformanceMonths' => [['value' => '2026-10', 'label' => 'October 2026'], ['value' => '2026-09', 'label' => 'September 2026']],
    'selectedProductMonth' => 'all', 'dashboardFromDate' => Carbon\Carbon::now()->startOfMonth()->toDateString(), 'dashboardToDate' => Carbon\Carbon::now()->endOfMonth()->toDateString(),
];
foreach (['en', 'km'] as $locale) {
    $app->setLocale($locale);
    file_put_contents($argv[1] . '/' . $locale . '.html', Illuminate\Support\Facades\Blade::render($source, $base));
    $filtered = array_merge($base, ['totalOrderPrice' => 125, 'pendingOrderCount' => 0, 'totalOrderRemainingAmount' => 100,
        'remainingOrderCount' => 1, 'productPerformance' => collect([array_merge($product, ['budget' => '$125.00'])])]);
    $empty = array_merge($base, ['totalOrderPrice' => 0, 'pendingOrderCount' => 0, 'totalOrderRemainingAmount' => 0,
        'remainingOrderCount' => 0, 'productPerformance' => collect(), 'recentTransactions' => collect()]);
    foreach (['default' => $base, 'filtered' => $filtered, 'empty' => $empty] as $state => $data) {
        foreach (['all', '2026-10', '2026-09'] as $month) {
            $data['selectedProductMonth'] = $month;
            if ($month === '2026-09') $data['productPerformance'] = collect();
            file_put_contents($argv[1] . "/$locale-$state-$month.json", json_encode([
                'html' => view('admin::pages.dashboard_content', $data)->render(),
                'products' => view('admin::pages.dashboard_product_performance_rows', $data)->render(),
            ]));
        }
    }
}
