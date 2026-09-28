<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Contracts\AudiencePolicy;
use App\Contracts\FileStorageLayout;
use App\Contracts\GuestAccessPolicy;
use App\Contracts\ThreadReplyAddress;
use App\Policies\DefaultAudiencePolicy;
use App\Policies\DefaultFileStorageLayout;
use App\Policies\DefaultGuestAccessPolicy;
use App\Policies\DefaultThreadReplyAddress;
use App\Services\Circles\SelectorService;
use App\Services\Pages\ReadsService;
use App\Services\Utils\Utf8DotenvEditor;
use Jackiedo\DotenvEditor\DotenvEditor;

class AppServiceProvider extends ServiceProvider
{
    public $singletons = [
        SelectorService::class => SelectorService::class,
        ReadsService::class => ReadsService::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(DotenvEditor::class, Utf8DotenvEditor::class);
        $this->app->bind(AudiencePolicy::class, DefaultAudiencePolicy::class);
        $this->app->bind(GuestAccessPolicy::class, DefaultGuestAccessPolicy::class);
        $this->app->bind(FileStorageLayout::class, DefaultFileStorageLayout::class);
        $this->app->bind(ThreadReplyAddress::class, DefaultThreadReplyAddress::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // MySQL5.7.7未満のときに 1071 Specified key was too long
        // エラーが発生しないようにする
        Schema::defaultStringLength(191);
    }
}
