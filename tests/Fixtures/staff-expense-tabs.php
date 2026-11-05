<?php
// Render real listing views with in-memory models; no application database access.
chdir(dirname(__DIR__, 2));
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'session.driver' => 'array']);
$app->setLocale('en');
$user = new App\Models\User(['name' => 'UI test', 'role' => 'super_admin']);
$user->setRelation('roles', collect());
$user->setRelation('permissions', collect());
Illuminate\Support\Facades\Auth::setUser($user);
Illuminate\Support\Facades\Gate::before(fn () => true);

$expense = new App\Models\StaffExpense([
    'type' => App\Enums\ExpenseType::SALARY, 'amount' => 350, 'expense_date' => '2026-11-05',
    'description' => 'Monthly salary', 'status' => 1,
]);
$expense->id = 1;
$expense->setRelation('staff', (new App\Models\Staff(['name' => 'Test staff']))->setAppends([]));
$expense->setRelation('shop', (new App\Models\Shop(['name' => 'Test shop']))->setAppends([]));
$expense->setRelation('createdBy', new App\Models\User(['name' => 'Test admin']));

foreach ([
    'staff-expense' => ['staffExpense/index', [$expense]],
    'stock-on-hand' => ['inventoryManagement/stockOnHand/index', []],
    'staff' => ['staff/index', []],
] as $module => [$view, $rows]) {
    $request = Illuminate\Http\Request::create('http://localhost/admin/' . $module . '/list/1');
    $app->instance('request', $request);
    $source = file_get_contents('resources/admin/views/pages/' . $view . '.blade.php');
    preg_match_all("/@section\('(layout|script)'\)(.*?)@stop/s", $source, $sections, PREG_SET_ORDER);
    foreach ($sections as $section) {
        $html = Illuminate\Support\Facades\Blade::render($section[2], [
            'routeName' => $module, 'status' => 1, 'shop' => null,
            'data' => new Illuminate\Pagination\LengthAwarePaginator($rows, count($rows), 10, 1, ['path' => $request->url()]),
        ]);
        file_put_contents($argv[1] . '/' . $module . '-' . $section[1] . '.html', $html);
    }
}
