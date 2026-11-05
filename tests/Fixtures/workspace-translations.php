<?php
// Render the real tab bar with the existing user-language middleware, without a database.
chdir(dirname(__DIR__, 2));
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'session.driver' => 'array']);
$user = new App\Models\User(['name' => 'Workspace test', 'role' => 'super_admin']);
$user->id = 1;
$user->setRelation('roles', collect());
$user->setRelation('permissions', collect());
Illuminate\Support\Facades\Auth::setUser($user);
Illuminate\Support\Facades\Gate::before(fn () => true);
$request = Illuminate\Http\Request::create('http://localhost/admin/dashboard');
$request->setUserResolver(fn () => $user);
$app->instance('request', $request);
$fixtures = [];
foreach (App\Support\Language::activeCodes() as $locale) {
    $user->language_preference = $locale;
    (new App\Http\Middleware\Localization())->handle($request, function () use (&$fixtures, $locale) {
        $fixtures[$locale] = [
            'bar' => view('admin::shared.workspace-tab-bar')->render(),
            'titles' => [
                '/admin/dashboard' => __('dashboard.title'),
                '/admin/product/list/1' => __('product.title'),
                '/admin/customer/create' => __('customer.form.title.create'),
                '/admin/customer/edit/42' => __('customer.form.title.update'),
                '/admin/user/list/1' => __('user.title'),
                '/admin/user/edit/1' => __('user.form.title.update'),
            ],
            'loading' => __('loading.loading'),
        ];
    });
}
echo json_encode($fixtures, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
