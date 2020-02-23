<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// ルートURL
Route::get('/', 'IndexController');

// トップページ
// TODO: URL は /home に変更する
Route::get('/home__v2', 'HomeAction')->name('home');

// お知らせ
Route::prefix('/pages')
    ->name('pages.')
    ->group(function () {
        Route::get('/', 'Pages\IndexAction')->name('index');
        Route::get('/{page}', 'Pages\ShowAction')->name('show');
    });

// 配布資料
Route::prefix('/documents')
    ->name('documents.')
    ->group(function () {
        Route::get('/', 'Documents\IndexAction')->name('index');
        Route::get('/{document}', 'Documents\ShowAction')->name('show');
    });

// スケジュール
Route::prefix('/schedules')
    ->name('schedules.')
    ->group(function () {
        Route::get('/', 'Schedules\IndexAction')->name('index');
        Route::get('/ended', 'Schedules\EndedAction')->name('ended');
        Route::get('/{schedule}', 'Schedules\ShowAction')->name('show');
    });

// 認証系
Auth::routes([
    'register' => true,
    'reset' => false,
    'verify' => false,
]);

// メール認証系
Route::prefix('/email')
    ->name('verification.')
    ->group(function () {
        Route::get('/verify', 'Auth\Email\VerifyNoticeAction')->name('notice')->middleware('auth');
        Route::get('/verify/{type}/{user}', 'Auth\Email\VerifyAction')->name('verify');
        Route::post('/resend', 'Auth\Email\ResendAction')->name('resend')->middleware('auth');
        Route::get('/verify/completed', 'Auth\Email\CompletedAction')->name('completed')
            ->middleware(['auth', 'verified']);
    });

// パスワードリセット系
Route::prefix('/password')
    ->name('password.')
    ->group(function () {
        Route::get('/reset', 'Auth\Password\ResetStartAction')->name('request');
        Route::post('/reset', 'Auth\Password\PostResetStartAction');

        Route::middleware('signed')
            ->group(function () {
                Route::get('/reset/{user}', 'Auth\Password\ResetPasswordAction')->name('reset');
                Route::post('/reset/{user}', 'Auth\Password\PostResetPasswordAction');
            });
    });

// ログインさえされていればアクセスできるルート
Route::middleware(['auth'])->group(function () {
    Route::get('/change_password', 'Users\ChangePasswordAction')->name('change_password');
    Route::post('/change_password', 'Users\PostChangePasswordAction');
    Route::get('/logout', 'Auth\LoginController@showLogout');
    Route::get('/user/edit', 'Users\EditInfoAction')->name('user.edit');
    Route::patch('/user/update', 'Users\UpdateInfoAction')->name('user.update');
    Route::get('/user/delete', 'Users\DeleteAction')->name('user.delete');
    Route::delete('/user', 'Users\DestroyAction')->name('user.destroy');
    // お問い合わせページ
    Route::get('/contacts', 'Contacts\CreateAction')->name('contacts');
    Route::post('/contacts', 'Contacts\PostAction')->name('contacts.post');

    // 団体セレクター (GETパラメーターの redirect に Route名 を入れる)
    Route::get('/selector', 'Circles\Selector\ShowAction')->name('circles.selector.show');
});

// ログインされており、メールアドレス認証が済んでいる場合のみアクセス可能なルート
Route::middleware(['auth', 'verified'])->group(function () {
    // 申請
    Route::prefix('/forms__v2')
        ->name('forms.')
        ->group(function () {
            Route::get('/', 'Forms\IndexAction')->name('index');
            Route::get('/closed', 'Forms\ClosedAction')->name('closed');
            Route::get('/all', 'Forms\AllAction')->name('all');

            Route::prefix('/{form}/answers')
                ->name('answers.')
                ->group(function () {
                    Route::get('/{answer}/edit', 'Forms\Answers\EditAction')->name('edit');
                    Route::patch('/{answer}', 'Forms\Answers\UpdateAction')->name('update');
                    Route::get('/create', 'Forms\Answers\CreateAction')->name('create');
                    Route::post('/', 'Forms\Answers\StoreAction')->name('store');
                    Route::get('/{answer}/uploads/{question}', 'Forms\Answers\Uploads\ShowAction')->name('uploads.show');
                });
        });
});

// スタッフページ（二段階認証も済んでいる状態）
Route::middleware(['auth', 'verified', 'can:staff', 'staffAuthed'])
    ->prefix('/staff')
    ->name('staff.')
    ->group(function () {
        // 申請フォームエディタ
        Route::prefix('/forms/{form}')
            ->name('forms.')
            ->group(function () {
                Route::get('/editor', 'Staff\Forms\EditorAction')->name('editor');
                // ↓「editor.api」のroute定義は resources/views/staff/forms/editor.blade.php で利用しているので、消さないこと
                Route::get('/editor/api/', 'Staff\Forms\EditorAPIAction')->name('editor.api');
                Route::get('/editor/api/get_form', 'Staff\Forms\GetFormAction');
                Route::post('/editor/api/update_form', 'Staff\Forms\UpdateFormAction');
                Route::get('/editor/api/get_questions', 'Staff\Forms\GetQuestionsAction');
                Route::post('/editor/api/add_question', 'Staff\Forms\AddQuestionAction');
                Route::post('/editor/api/update_questions_order', 'Staff\Forms\UpdateQuestionsOrderAction');
                Route::post('/editor/api/update_question', 'Staff\Forms\UpdateQuestionAction');
                Route::post('/editor/api/delete_question', 'Staff\Forms\DeleteQuestionAction');
            });

        // メール一斉送信
        Route::get('/send_emails', 'Staff\SendEmails\ListAction')->name('send_emails');
        Route::post('/send_emails', 'Staff\SendEmails\StoreAction');
        Route::delete('/send_emails', 'Staff\SendEmails\DestroyAction');

        // 団体情報編集
        Route::get('/circles/{circle}/edit', 'Staff\Circles\EditAction')->name('circles.edit');
        Route::patch('/circles/{circle}', 'Staff\Circles\UpdateAction')->name('circles.update');
        Route::get('/circles/create', 'Staff\Circles\CreateAction')->name('circles.create');
        Route::post('/circles', 'Staff\Circles\StoreAction')->name('circles.new');

        // ユーザーチェッカー
        Route::get('/users/check', 'Staff\Users\CheckerAction')->name('users.check');
        Route::get('/users/check/list', 'Staff\Users\CheckerListAction')->name('users.check.list');
    });
