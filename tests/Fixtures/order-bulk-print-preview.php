<?php
// Render the real preview and invoice templates with in-memory models, without application data.
chdir(dirname(__DIR__, 2));
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'session.driver' => 'array']);
$request = Illuminate\Http\Request::create($argv[1]);
$app->instance('request', $request);
$locale = $request->query('locale', 'en');
$app->setLocale($locale);
$documentType = $request->query('type', 'invoice');
$count = min(100, max(1, (int) $request->query('count', 3)));
$orders = collect(range(44, 45 - $count))->map(function ($id) {
    $order = new App\Models\Order();
    $order->forceFill([
        'id' => $id, 'invoice_number' => sprintf('NO-%04d', $id),
        'order_date' => '2026-10-30 10:00:00', 'delivery_date' => '2026-10-30',
        'total_price' => 20, 'paid_amount' => 0, 'payment_status' => 'Pending',
    ]);
    $order->setRelation('shop', new App\Models\Shop(['name' => 'Preview Test Shop', 'phone' => '012345678', 'address' => 'Test Address']));
    $order->setRelation('customer', new App\Models\Customer(['name' => 'Customer ' . $id, 'phone' => '012345678', 'address' => 'Test Address']));
    $order->setRelation('barber', null);
    $order->setRelation('createdBy', null);
    $order->setRelation('payments', collect());
    $detail = new App\Models\OrderDetail(['type' => 'product', 'qty' => 2, 'price' => 10, 'product_discount' => 0]);
    $product = new App\Models\Product(['name' => 'Invoice product ' . $id]);
    $product->setRelation('uom', null);
    $detail->setRelation('product', $product);
    $order->setRelation('orderDetails', collect([$detail]));
    return $order;
});
$items = $orders->map(fn ($order) => [
    'order' => $order, 'printLocale' => $locale, 'invoicePreviousBalance' => 5.00,
    'customerName' => $order->customer->name, 'customerPhone' => $order->customer->phone,
    'customerAddress' => $order->customer->address, 'printAreaId' => 'fixture_invoice_' . $order->id,
])->all();
$source = file_get_contents('resources/admin/views/pages/order-bulk-print/preview.blade.php');
$source = preg_replace('/^@(extends|section|stop).*$/m', '', $source);
$source = preg_replace("/^\s*@include\('admin::shared.header'.*$/m", '', $source);
echo Illuminate\Support\Facades\Blade::render($source, [
    'items' => $items, 'orders' => $orders, 'locale' => $locale, 'documentType' => $documentType,
    'totalCount' => count($items), 'consolidatedProducts' => collect(),
]);
