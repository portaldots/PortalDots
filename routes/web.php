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
});

// ログインされており、メールアドレス認証が済んでいる場合のみアクセス可能なルート
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/home', 'HomeController@index')->name('home');
});

// スタッフページ
Route::middleware(['auth', 'verified', 'can:staff'])
    ->prefix('/staff')
    ->name('staff.')
    ->group(function () {
        // staff.forms.visual_editor
        Route::get('/forms/visual_editor/{form}', 'Staff\Forms\VisualEditorAction')->name('forms.visual_editor');
        Route::post('/forms/visual_editor/{form}/api')->name('forms.visual_editor.api');
    });
