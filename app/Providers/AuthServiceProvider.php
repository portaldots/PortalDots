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
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        App\Eloquents\Page::class => App\Policies\PagePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // 管理者で、メール認証やスタッフ認証が済んでいる場合、
        // auth()->user->can() や @can() などで true を返すようにする
        Gate::after(function (User $user) {
            if (config('portal.enable_demo_mode')) {
                // デモモードの場合は許可
                return true;
            }

            return $user->is_admin && $user->areBothEmailsVerified() &&
                session()->get('staff_authorized') ? true : null;
        });


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

        // 管理者
        Gate::define('admin', function (User $user) {
            return $user->is_admin === true;
        });

        Gate::define('circle.belongsTo', 'App\Policies\Circle\BelongsPolicy');
        Gate::define('circle.update', 'App\Policies\Circle\UpdatePolicy');
        Gate::define('circle.create', 'App\Policies\Circle\CreatePolicy');
    }
}
