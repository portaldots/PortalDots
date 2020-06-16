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
            Route::get('/create', 'Circles\CreateAction')->name('create');
            Route::post('/', 'Circles\StoreAction')->name('store');
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
            // 参加登録状況
            Route::get('/{circle}/status', 'Circles\StatusAction')->name('status');
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

// スタッフページ（多要素認証も済んでいる状態）
Route::middleware(['auth', 'verified', 'can:staff', 'staffAuthed'])
    ->prefix('/staff')
    ->name('staff.')
    ->group(function () {
        // Markdown ガイド
        //
        // 外部サイトにしてしまうとリンク切れが発生する恐れがあるため、
        // PortalDots 内部に Markdown ガイドを用意した
        //
        // このページのURLを変更する場合は
        // resources/js/v2/components/MarkdownEditor.vue
        // 内に含まれるこのページへのURLも修正すること
        Route::view('/markdown-guide', 'v2.staff.markdown_guide')
            ->name('markdown-guide');

        // お知らせ
        Route::prefix('/pages')
            ->name('pages.')
            ->group(function () {
                Route::get('/create', 'Staff\Pages\CreateAction')->name('create');
                Route::post('/', 'Staff\Pages\StoreAction')->name('store');
                Route::get('/{page}/edit', 'Staff\Pages\EditAction')->name('edit');
                Route::patch('/{page}', 'Staff\Pages\UpdateAction')->name('update');
            });

        // 申請
        Route::prefix('/forms/{form}')
            ->name('forms.')
            ->group(function () {
                // 回答確認
                Route::prefix('/answers')
                    ->name('answers.')
                    ->group(function () {
                        Route::get('/{answer}/edit', 'Staff\Forms\Answers\EditAction')->name('edit');
                        Route::patch('/{answer}', 'Staff\Forms\Answers\UpdateAction')->name('update');
                        Route::get('/create', 'Staff\Forms\Answers\CreateAction')->name('create');
                        Route::post('/', 'Staff\Forms\Answers\StoreAction')->name('store');
                        Route::get('/{answer}/uploads/{question}', 'Staff\Forms\Answers\Uploads\ShowAction')->name('uploads.show');
                        Route::get('/uploads', 'Staff\Forms\Answers\Uploads\IndexAction')->name('uploads.index');
                        Route::post('/uploads/download_zip', 'Staff\Forms\Answers\Uploads\DownloadZipAction')->name('uploads.download_zip');
                    });

                // 申請フォームエディタ
                Route::prefix('/editor')
                    ->group(function () {
                        Route::get('/', 'Staff\Forms\Editor\IndexAction')->name('editor');
                        // ↓「editor.api」のroute定義は resources/views/staff/forms/editor.blade.php で利用しているので、消さないこと
                        Route::get('/api', 'Staff\Forms\Editor\APIAction')->name('editor.api');
                        Route::get('/api/get_form', 'Staff\Forms\Editor\GetFormAction');
                        Route::post('/api/update_form', 'Staff\Forms\Editor\UpdateFormAction');
                        Route::get('/api/get_questions', 'Staff\Forms\Editor\GetQuestionsAction');
                        Route::post('/api/add_question', 'Staff\Forms\Editor\AddQuestionAction');
                        Route::post('/api/update_questions_order', 'Staff\Forms\Editor\UpdateQuestionsOrderAction');
                        Route::post('/api/update_question', 'Staff\Forms\Editor\UpdateQuestionAction');
                        Route::post('/api/delete_question', 'Staff\Forms\Editor\DeleteQuestionAction');
                    });

                Route::get('/not_answered', 'Staff\Forms\Answers\NotAnswered\ShowAction');

                // フォームの複製
                // TODO: CopyConfirmAction は、CodeIgniter から CopyAction へ直接 POST できない都合で挟んだクッションページなので、
                // スタッフモードが Laravel 化したら CopyConfirmAction は消す。
                Route::get('/copy', 'Staff\Forms\CopyConfirmAction')->name('copy');
                Route::post('/copy', 'Staff\Forms\CopyAction');
            });

        Route::prefix('/circles')
            ->name('circles.')
            ->group(function () {
                // 参加登録設定
                Route::get('/custom_form', 'Staff\Circles\CustomForm\IndexAction')->name('custom_form.index');
                Route::post('/custom_form', 'Staff\Circles\CustomForm\StoreAction')->name('custom_form.store');
                Route::patch('/custom_form', 'Staff\Circles\CustomForm\UpdateAction')->name('custom_form.update');

                // 企画情報編集
                Route::get('/{circle}/edit', 'Staff\Circles\EditAction')->name('edit');
                Route::patch('/{circle}', 'Staff\Circles\UpdateAction')->name('update');
                Route::get('/create', 'Staff\Circles\CreateAction')->name('create');
                Route::post('/', 'Staff\Circles\StoreAction')->name('new');

                // 企画所属者宛のメール送信
                Route::get('/{circle}/email', 'Staff\Circles\SendEmails\IndexAction')->name('email');
                Route::post('/{circle}/email', 'Staff\Circles\SendEmails\SendAction');

                // 企画情報エクスポート
                Route::get('/export', 'Staff\Circles\ExportAction')->name('export');
            });

        // メール一斉送信
        Route::get('/send_emails', 'Staff\SendEmails\ListAction')->name('send_emails');
        Route::post('/send_emails', 'Staff\SendEmails\StoreAction');
        Route::delete('/send_emails', 'Staff\SendEmails\DestroyAction');

        // スタッフが手動でメール認証を完了する
        Route::get('/users/{user}/verify', 'Staff\Users\Verify\IndexAction')->name('users.verify');
        Route::patch('/users/{user}', 'Staff\Users\Verify\UpdateAction')->name('users.verify.update');

        Route::prefix('/contacts')
            ->name('contacts.')
            ->group(function () {
                // お問い合わせのメールリスト
                Route::get('/categories', 'Staff\Contacts\Categories\IndexAction')->name('categories.index');
                Route::get('/categories/create', 'Staff\Contacts\Categories\CreateAction')->name('categories.create');
                Route::post('/categories/create', 'Staff\Contacts\Categories\StoreAction');
                Route::get('/categories/{category}/edit', 'Staff\Contacts\Categories\EditAction')->name('categories.edit');
                Route::patch('/categories/{category}', 'Staff\Contacts\Categories\UpdateAction')->name('categories.update');
                Route::get('/categories/{category}/delete', 'Staff\Contacts\Categories\DeleteAction')->name('categories.delete');
                Route::delete('/categories/{category}', 'Staff\Contacts\Categories\DestroyAction')->name('categories.destroy');
            });

        // 配布資料
        Route::prefix('/documents')
            ->name('documents.')
            ->group(function () {
                Route::get('/create', 'Staff\Documents\CreateAction')->name('create');
                Route::post('/', 'Staff\Documents\StoreAction')->name('store');
                Route::get('/{document}/edit', 'Staff\Documents\EditAction')->name('edit');
                Route::patch('/{document}', 'Staff\Documents\UpdateAction')->name('update');
                Route::get('/{document}', 'Staff\Documents\ShowAction')->name('show');
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
