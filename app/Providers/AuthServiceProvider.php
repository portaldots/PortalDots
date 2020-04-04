<?php

declare(strict_types=1);

namespace App\Providers;

use App\Auth\AppUserProvider;
use App\Eloquents\User;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::guessPolicyNamesUsing(function ($modelClass) {
            return 'App\\Policies\\' . class_basename($modelClass) . 'Policy';
        });

        Auth::provider('app', function (Application $app, array $config) {
            return new AppUserProvider($app['hash'], $config['model']);
        });

        // メール認証が完了している場合のみ使える機能
        Gate::define('use-all-features', function (User $user) {
            return $user->areBothEmailsVerified();
        });

        // スタッフ
        Gate::define('staff', function (User $user) {
            return $user->is_staff === true;
        });

        Gate::define('circle.belongsTo', 'App\Policies\Circle\BelongsPolicy');
        Gate::define('circle.update', 'App\Policies\Circle\UpdatePolicy');
        Gate::define('circle.create', 'App\Policies\Circle\CreatePolicy');
    }
}
