<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Auth;
use Request;

class BladeServiceProvider extends ServiceProvider
{
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
        // スタッフページかどうかを Blade 上で判断できるようにする
        // @staffpage 〜 @endstaffpage
        // の中は、スタッフページの場合のみ表示される
        Blade::if('staffpage', function () {
            return Request::is('staff*');
        });

        // 渡された引数の文字列をMarkdownとして解釈し、
        // HTMLに変換した文字列を表示する
        Blade::directive('markdown', function ($expression) {
            return "<?php echo App\Services\Utils\ParseMarkdownService::render($expression); ?>";
        });

        // 渡された引数の文字列を先頭100文字のみのこし、
        // 残りを「...」で省略する
        Blade::directive('summary', function ($expression) {
            return "<?php echo e(App\Services\Utils\FormatTextService::summary($expression)); ?>";
        });

        // 渡された引数の日付文字列をY年n月d日(曜日) H:i 形式の日付文字列にする
        Blade::directive('datetime', function ($expression) {
            return "<?php echo e(App\Services\Utils\FormatTextService::datetime($expression)); ?>";
        });

        // 渡された引数の曜日番号を曜日文字列にする
        Blade::directive('dayByDayId', function ($expression) {
            return "<?php echo e(App\Services\Utils\FormatTextService::getDayByDayId((int)($expression), true)); ?>";
        });
    }
}
