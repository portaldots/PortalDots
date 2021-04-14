<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "staff" middleware group. Now create something great!
|
*/

// スタッフページ（多要素認証も済んでいる状態）
Route::middleware(['auth', 'verified', 'can:staff', 'staffAuthed'])
    ->prefix('/staff')
    ->name('staff.')
    ->group(function () {
        // トップページ
        Route::get('/', 'Staff\HomeAction')->name('index');

        // Markdown ガイド
        //
        // 外部サイトにしてしまうとリンク切れが発生する恐れがあるため、
        // PortalDots 内部に Markdown ガイドを用意した
        //
        // このページのURLを変更する場合は
        // resources/js/v2/components/MarkdownEditor.vue
        // 内に含まれるこのページへのURLも修正すること
        Route::view('/markdown-guide', 'staff.markdown_guide')
            ->name('markdown-guide');

        // お知らせ
        Route::prefix('/pages')
            ->name('pages.')
            ->group(function () {
                Route::get('/', 'Staff\Pages\IndexAction')->name('index');
                Route::get('/api', 'Staff\Pages\ApiAction')->name('api');
                Route::get('/create', 'Staff\Pages\CreateAction')->name('create');
                Route::post('/', 'Staff\Pages\StoreAction')->name('store');
                Route::get('/{page}/edit', 'Staff\Pages\EditAction')->name('edit');
                Route::patch('/{page}', 'Staff\Pages\UpdateAction')->name('update');
                Route::get('/export', 'Staff\Pages\ExportAction')->name('export');
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

                Route::get('/export', 'Staff\Forms\Answers\ExportAction')->name('export');
            });

        Route::prefix('/users')
            ->name('users.')
            ->group(function () {
                Route::get('/', 'Staff\Users\IndexAction')->name('index');
                Route::get('/api', 'Staff\Users\ApiAction')->name('api');
                Route::get('/export', 'Staff\Users\ExportAction')->name('export');

                // 手動本人確認
                Route::get('/{user}/verify', 'Staff\Users\VerifyConfirmAction')->name('verify');
                Route::patch('/{user}/verify', 'Staff\Users\VerifiedAction')->name('verified');
            });

        Route::prefix('/circles')
            ->name('circles.')
            ->group(function () {
                Route::get('/', 'Staff\Circles\IndexAction')->name('index');
                Route::get('/api', 'Staff\Circles\ApiAction')->name('api');

                // 参加登録設定
                Route::get('/custom_form', 'Staff\Circles\CustomForm\IndexAction')->name('custom_form.index');
                Route::post('/custom_form', 'Staff\Circles\CustomForm\StoreAction')->name('custom_form.store');
                Route::patch('/custom_form', 'Staff\Circles\CustomForm\UpdateAction')->name('custom_form.update');

                // 企画情報編集
                Route::get('/{circle}/edit', 'Staff\Circles\EditAction')->name('edit');
                Route::patch('/{circle}', 'Staff\Circles\UpdateAction')->name('update');
                Route::get('/create', 'Staff\Circles\CreateAction')->name('create');
                Route::post('/', 'Staff\Circles\StoreAction')->name('store');

                // 企画所属者宛のメール送信
                Route::get('/{circle}/email', 'Staff\Circles\SendEmails\IndexAction')->name('email');
                Route::post('/{circle}/email', 'Staff\Circles\SendEmails\SendAction');

                // 企画情報エクスポート
                Route::get('/export', 'Staff\Circles\ExportAction')->name('export');

                Route::delete('/{circle}', 'Staff\Circles\DestroyAction')->name('destroy');
            });

        Route::prefix('/tags')
            ->name('tags.')
            ->group(function () {
                Route::get('/', 'Staff\Tags\IndexAction')->name('index');
                Route::get('/api', 'Staff\Tags\ApiAction')->name('api');
                Route::get('/create', 'Staff\Tags\CreateAction')->name('create');
                Route::post('/', 'Staff\Tags\StoreAction')->name('store');
                Route::get('/{tag}/edit', 'Staff\Tags\EditAction')->name('edit');
                Route::patch('/{tag}', 'Staff\Tags\UpdateAction')->name('update');
                Route::get('/{tag}/delete', 'Staff\Tags\DeleteAction')->name('delete');
                Route::delete('/{tag}', 'Staff\Tags\DestroyAction')->name('destroy');
                Route::get('/export', 'Staff\Tags\ExportAction')->name('export');
            });

        Route::prefix('/places')
            ->name('places.')
            ->group(function () {
                Route::get('/', 'Staff\Places\IndexAction')->name('index');
                Route::get('/api', 'Staff\Places\ApiAction')->name('api');
                Route::get('/create', 'Staff\Places\CreateAction')->name('create');
                Route::post('/', 'Staff\Places\StoreAction')->name('store');
                Route::get('/{place}/edit', 'Staff\Places\EditAction')->name('edit');
                Route::patch('/{place}', 'Staff\Places\UpdateAction')->name('update');
                Route::delete('/{place}', 'Staff\Places\DestroyAction')->name('destroy');
                Route::get('/export', 'Staff\Places\ExportAction')->name('export');
            });

        Route::prefix('/schedules')
            ->name('schedules.')
            ->group(function () {
                Route::get('/', 'Staff\Schedules\IndexAction')->name('index');
                Route::get('/api', 'Staff\Schedules\ApiAction')->name('api');
                Route::get('/create', 'Staff\Schedules\CreateAction')->name('create');
                Route::post('/', 'Staff\Schedules\StoreAction')->name('store');
                Route::get('/{schedule}/edit', 'Staff\Schedules\EditAction')->name('edit');
                Route::patch('/{schedule}', 'Staff\Schedules\UpdateAction')->name('update');
                Route::delete('/{schedule}', 'Staff\Schedules\DestroyAction')->name('destroy');
            });

        // メール一斉送信
        Route::get('/send_emails', 'Staff\SendEmails\ListAction')->name('send_emails');
        Route::delete('/send_emails', 'Staff\SendEmails\DestroyAction');

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
                Route::get('/', 'Staff\Documents\IndexAction')->name('index');
                Route::get('/api', 'Staff\Documents\ApiAction')->name('api');
                Route::get('/create', 'Staff\Documents\CreateAction')->name('create');
                Route::post('/', 'Staff\Documents\StoreAction')->name('store');
                Route::get('/{document}/edit', 'Staff\Documents\EditAction')->name('edit');
                Route::patch('/{document}', 'Staff\Documents\UpdateAction')->name('update');
                Route::get('/{document}', 'Staff\Documents\ShowAction')->name('show');
            });
    });
