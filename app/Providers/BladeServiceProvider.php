<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View as FacadeView;
use Illuminate\View\View;
use Auth;
use Request;
use App\Eloquents\Page;
use App\Services\Circles\SelectorService;

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
    public function boot(SelectorService $selectorService)
    {
        // スタッフページかどうかを Blade 上で判断できるようにする
        // @staffpage 〜 @endstaffpage
        // の中は、スタッフページの場合のみ表示される
        Blade::if('staffpage', function () {
            return Request::is('staff*');
        });

        // お知らせの未読数を表示するためのビューコンポーザ
        FacadeView::composer('*', function (View $view) use ($selectorService) {
            $pages_unread_count = 0;

            if (Auth::check()) {
                $pages = Page::byCircle($selectorService->getCircle())
                    ->with(['usersWhoRead' => function ($query) {
                        $query->where('user_id', Auth::id());
                    }])->get();
                $pages_unread_count = $pages->reduce(function (int $carry, Page $page) {
                    if ($page->usersWhoRead->isEmpty()) {
                        return $carry + 1;
                    }
                    return $carry;
                }, 0);
            }

            $view->with('pages_unread_count', $pages_unread_count);
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

        // 渡された引数のバイト数値からユーザーフレンドリーなファイルサイズ文字列にする
        Blade::directive('filesize', function ($expression) {
            return "<?php echo e(App\Services\Utils\FormatTextService::filesize((int)($expression))); ?>";
        });
    }
}
