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

// トップページ
Route::get('/', 'HomeAction')->middleware(['circleSelected'])->name('home');

// お知らせ
Route::prefix('/pages')
    ->name('pages.')
    ->middleware(['circleSelected'])
    ->group(function () {
        Route::get('/', 'Pages\IndexAction')->name('index');
        Route::get('/{page}', 'Pages\ShowAction')->name('show');
    });

// 配布資料
Route::prefix('/documents')
    ->name('documents.')
    ->middleware(['circleSelected'])
    ->group(function () {
        Route::get('/', 'Documents\IndexAction')->name('index');
        Route::get('/{document}', 'Documents\ShowAction')->name('show');
    });

// スケジュール
Route::prefix('/schedules')
    ->name('schedules.')
    ->middleware(['circleSelected'])
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
    Route::get('/logout', 'Auth\LoginController@showLogout');
    Route::get('/user/edit', 'Users\EditInfoAction')->name('user.edit');
    Route::patch('/user/update', 'Users\UpdateInfoAction')->name('user.update');
    Route::get('/user/password', 'Users\ChangePasswordAction')->name('user.password');
    Route::post('/user/password', 'Users\PostChangePasswordAction');
    Route::get('/user/delete', 'Users\DeleteAction')->name('user.delete');
    Route::delete('/user', 'Users\DestroyAction')->name('user.destroy');
    // お問い合わせページ
    Route::get('/contacts', 'Contacts\CreateAction')->name('contacts');
    Route::post('/contacts', 'Contacts\PostAction')->name('contacts.post');

    // 企画セレクター (GETパラメーターの redirect に Route名 を入れる)
    Route::get('/selector', 'Circles\Selector\ShowAction')->name('circles.selector.show');
    Route::get('/selector/set', 'Circles\Selector\SetAction')->name('circles.selector.set');
});

// ログインされており、メールアドレス認証が済んでいる場合のみアクセス可能なルート
Route::middleware(['auth', 'verified'])->group(function () {
    // 企画参加登録
    Route::prefix('/circles')
        ->name('circles.')
        ->group(function () {
            Route::get('/terms', 'Circles\TermsAction')->name('terms');
            Route::get('/create', 'Circles\CreateAction')->name('create');
            Route::post('/', 'Circles\StoreAction')->name('store');
            Route::get('/{circle}', 'Circles\ShowAction')->name('show');
            Route::get('/{circle}/edit', 'Circles\EditAction')->name('edit');
            Route::patch('/{circle}', 'Circles\UpdateAction')->name('update');
            // 企画メンバー登録関連
            Route::get('/{circle}/users', 'Circles\Users\IndexAction')->name('users.index');
            Route::get('/{circle}/users/invite/{token}', 'Circles\Users\InviteAction')->name('users.invite');
            Route::post('/{circle}/users', 'Circles\Users\StoreAction')->name('users.store');
            Route::delete('/{circle}/users/{user}', 'Circles\Users\DestroyAction')->name('users.destroy');
            Route::post('/{circle}/users/regenerate', 'Circles\Users\RegenerateTokenAction')->name('users.regenerate');
            // 参加登録の提出
            Route::get('/{circle}/confirm', 'Circles\ConfirmAction')->name('confirm');
            Route::post('/{circle}/submit', 'Circles\SubmitAction')->name('submit');
            // 参加登録の削除
            Route::get('/{circle}/delete', 'Circles\DeleteAction')->name('delete');
            Route::delete('/{circle}', 'Circles\DestroyAction')->name('destroy');
        });

    // 申請
    Route::prefix('/forms')
        ->middleware(['circleSelected'])
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

// 管理者ページ（多要素認証も済んでいる状態）
Route::middleware(['auth', 'verified', 'can:admin', 'staffAuthed'])
    ->prefix('/admin')
    ->name('admin.')
    ->group(function () {
        // ポータル情報編集
        Route::get('/portal', 'Admin\Portal\EditAction')->name('portal.edit');
        Route::patch('/portal', 'Admin\Portal\UpdateAction')->name('portal.update');
    });
