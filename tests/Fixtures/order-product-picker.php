<?php
// Render the real order form and ConfirmDialog without the authenticated shell or a database.
chdir(dirname(__DIR__, 2));
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'session.driver' => 'array']);
$app->setLocale('en');
$app->instance('request', Illuminate\Http\Request::create('http://localhost/admin/order/create'));
$source = file_get_contents('resources/admin/views/pages/order/createOrder.blade.php');
$source = preg_replace("/^@(extends|section|include|stop).*$/m", '', $source);
$source = preg_replace("/^\s*@include\('admin::shared.header'.*$/m", '', $source);
$shop = ['id' => 1, 'name' => 'Central Shop'];
$order = ['id' => 123, 'shop_id' => 1, 'shop' => $shop, 'payment_status' => 'Pending', 'order_date' => '2026-10-18',
    'order_details' => [['id' => 99, 'type' => 'product', 'product_id' => 1, 'qty' => 3,
        'price' => 15, 'product_discount' => 0.5, 'product_discount_type' => 'usd',
        'product' => ['id' => 1, 'name' => 'Existing product']]]];
foreach (['create', 'edit', 'automatic', 'locked', 'no-shop'] as $mode) {
    $data = in_array($mode, ['edit', 'automatic', 'locked']) ? (object) $order : null;
    if ($mode === 'automatic') {
        $data->order_details[0] = array_merge($data->order_details[0], [
            'qty' => 2, 'price' => 10, 'original_price' => '9.30', 'unit_price' => '10.00',
            'product_discount' => 0, 'pricing_method' => 'auto_unit_price_v1',
        ]);
    }
    if ($mode === 'locked') $data->payment_status = 'Paid';
    $html = Illuminate\Support\Facades\Blade::render($source, [
        'data' => $data, 'id' => $data->id ?? null, 'selectedShop' => $mode === 'no-shop' ? null : $shop,
        'routeName' => 'order', 'recentOrders' => collect(),
    ]);
    file_put_contents($argv[1] . '/' . $mode . '.html', $html);
}
file_put_contents($argv[1] . '/dialog.html', view('admin::components.verify')->render());
echo "Rendered Create, legacy/automatic Edit, locked order and no-shop fixtures.\n";
