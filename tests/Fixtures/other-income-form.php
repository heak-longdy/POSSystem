<?php
// Render the actual Other Income form without the admin shell or application data.
chdir(dirname(__DIR__, 2));
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'session.driver' => 'array']);
$app->instance('request', Illuminate\Http\Request::create('http://localhost/admin/other-income/create'));
$source = file_get_contents('resources/admin/views/pages/otherIncome/store.blade.php');
$source = preg_replace('/^@(extends|section|stop).*$/m', '', $source);
$source = preg_replace("/^\s*@include\('admin::shared.header'.*$/m", '', $source);
foreach (['en', 'km'] as $locale) {
    $app->setLocale($locale);
    $html = Illuminate\Support\Facades\Blade::render($source, [
        'data' => null,
        'routeName' => 'other-income',
        'categories' => collect([(object) ['id' => 1, 'name' => 'Other income']]),
        'suppliers' => collect([(object) ['id' => 1, 'name' => 'Test supplier']]),
        'shops' => collect([(object) ['id' => 1, 'name' => 'Test shop']]),
        'quarters' => [['key' => '2026-Q4', 'label' => '2026 Q4']],
        'errors' => new Illuminate\Support\ViewErrorBag(),
    ]);
    file_put_contents($argv[1] . '/' . $locale . '.html', $html);
}
