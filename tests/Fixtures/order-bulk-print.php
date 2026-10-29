<?php
// Render the real listing with paginated fixtures, without application data or the admin shell.
chdir(dirname(__DIR__, 2));
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'session.driver' => 'array']);
$request = Illuminate\Http\Request::create($argv[1]);
$app->instance('request', $request);
$app->setLocale('en');
$user = new App\Models\User();
$user->id = (int) $request->query('user', 1);
auth()->setUser($user);
$status = $request->query('status', 'all');
$total = $status === 'Paid' ? 55 : 105;
$page = (int) $request->query('page', 1);
$orders = collect(range($total, 1))->slice(($page - 1) * 50, 50)->map(fn ($id) => (object) [
    'id' => $id,
    'display_invoice_number' => 'INV-' . $id,
    'display_date' => '2026-10-29',
    'display_shop_name' => 'Central',
    'display_customer_name' => 'Customer ' . $id,
    'display_total_price' => '$100.00',
    'display_payment_status' => $status === 'Paid' ? 'Paid' : 'Pending',
]);
$source = file_get_contents('resources/admin/views/pages/order-bulk-print/index.blade.php');
$source = preg_replace('/^@(extends|section|endsection).*$/m', '', $source);
$source = preg_replace("/^\s*@include\('admin::shared.header'.*$/m", '', $source);
echo Illuminate\Support\Facades\Blade::render($source, [
    'orders' => (new Illuminate\Pagination\LengthAwarePaginator($orders, $total, 50, $page, ['path' => $request->url()]))->appends($request->query()),
    'shops' => collect([(object) ['id' => 1, 'name' => 'Central']]),
    'status' => $status,
    'paymentStatus' => $status === 'all' ? null : $status,
    'fromDate' => $request->query('from_date', ''),
    'toDate' => $request->query('to_date', ''),
    'search' => $request->query('search', ''),
    'maxOrders' => 100,
    'maxDetailRows' => 2000,
]);
