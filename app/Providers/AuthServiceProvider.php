<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;
// use Laravel\Passport\Console\ClientCommand;
// use Laravel\Passport\Console\InstallCommand;
// use Laravel\Passport\Console\KeysCommand;
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        if (!$this->app->routesAreCached()) {
            Passport::routes();
        }

        Gate::before(function ($user, $ability) {
            return $user->role === 'super_admin';
        });

        // $this->commands([
        //     InstallCommand::class,
        //     ClientCommand::class,
        //     KeysCommand::class,
        // ]);
    }
}
