<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Services\Circles\SelectorService;

class AppServiceProvider extends ServiceProvider
{
    public $singletons = [
        SelectorService::class => SelectorService::class,
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // MySQL5.7.7未満のときに 1071 Specified key was too long
        // エラーが発生しないようにする
        Schema::defaultStringLength(191);
    }
}
