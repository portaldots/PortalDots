<?php

use Illuminate\Support\Facades\Route;

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

// スタッフ認証
Route::middleware(['auth', 'verified', 'can:staff'])
    ->prefix('/staff/verify')
    ->name('staff.verify.')
    ->group(function () {
        Route::get('/', 'Staff\Verify\IndexAction')->name('index');
        Route::post('/', 'Staff\Verify\VerifyAction');
    });

// スタッフページ（多要素認証も済んでいる状態）
Route::middleware(['auth', 'verified', 'can:staff', 'staffAuthed'])
    ->prefix('/staff')
    ->name('staff.')
    ->group(function () {
        // トップページ
        Route::get('/', 'Staff\HomeAction')->name('index');

        // リリース情報
        Route::get('/about', 'Staff\AboutAction')->name('about');

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
                Route::get('/', 'Staff\Pages\IndexAction')->name('index')->middleware(['can:staff.pages.read']);
                Route::get('/api', 'Staff\Pages\ApiAction')->name('api')->middleware(['can:staff.pages.read']);
                Route::get('/create', 'Staff\Pages\CreateAction')->name('create')->middleware(['can:staff.pages.edit']);
                Route::post('/', 'Staff\Pages\StoreAction')->name('store')->middleware(['can:staff.pages.edit']);
                Route::get('/{page}/edit', 'Staff\Pages\EditAction')->name('edit')->middleware(['can:staff.pages.edit']);
                Route::patch('/{page}', 'Staff\Pages\UpdateAction')->name('update')->middleware(['can:staff.pages.edit']);
                Route::delete('/{page}', 'Staff\Pages\DestroyAction')->name('destroy')->middleware(['can:staff.pages.delete']);
                Route::patch('/{page}/pin', 'Staff\Pages\PatchPinAction')->name('pin')->middleware(['can:staff.pages.edit']);
                Route::get('/export', 'Staff\Pages\ExportAction')->name('export')->middleware(['can:staff.pages.export']);
            });

        // 申請
        Route::prefix('/forms')
            ->name('forms.')
            ->group(function () {
                Route::get('/', 'Staff\Forms\IndexAction')->name('index')->middleware(['can:staff.forms.read']);
                Route::get('/api', 'Staff\Forms\ApiAction')->name('api')->middleware(['can:staff.forms.read']);
                Route::get('/create', 'Staff\Forms\CreateAction')->name('create')->middleware(['can:staff.forms.edit']);
                Route::post('/', 'Staff\Forms\StoreAction')->name('store')->middleware(['can:staff.forms.edit']);
                Route::get('/{form}/edit', 'Staff\Forms\EditAction')->name('edit')->middleware(['can:staff.forms.edit']);
                Route::patch('/{form}', 'Staff\Forms\UpdateAction')->name('update')->middleware(['can:staff.forms.edit']);
                Route::delete('/{form}', 'Staff\Forms\DestroyAction')->name('destroy')->middleware(['can:staff.forms.delete']);
                Route::get('/export', 'Staff\Forms\ExportAction')->name('export')->middleware(['can:staff.forms.export']);
            });

        // 申請個別ページ
        Route::prefix('/forms/{form}')
            ->name('forms.')
            ->group(function () {
                // 回答確認
                Route::prefix('/answers')
                    ->name('answers.')
                    ->group(function () {
                        Route::get('/', 'Staff\Forms\Answers\IndexAction')->name('index')->middleware(['can:staff.forms.answers.read']);
                        Route::get('/api', 'Staff\Forms\Answers\ApiAction')->name('api')->middleware(['can:staff.forms.answers.read']);
                        Route::get('/{answer}/edit', 'Staff\Forms\Answers\EditAction')->name('edit')->middleware(['can:staff.forms.answers.edit']);
                        Route::patch('/{answer}', 'Staff\Forms\Answers\UpdateAction')->name('update')->middleware(['can:staff.forms.answers.edit']);
                        Route::get('/create', 'Staff\Forms\Answers\CreateAction')->name('create')->middleware(['can:staff.forms.answers.edit']);
                        Route::post('/', 'Staff\Forms\Answers\StoreAction')->name('store')->middleware(['can:staff.forms.answers.edit']);
                        Route::get('/{answer}/uploads/{question}', 'Staff\Forms\Answers\Uploads\ShowAction')->name('uploads.show')->middleware(['can:staff.forms.answers.read']);
                        Route::get('/uploads', 'Staff\Forms\Answers\Uploads\IndexAction')->name('uploads.index')->middleware(['can:staff.forms.answers.export'])->middleware(['can:staff.forms.answers.export']);
                        Route::post('/uploads/download_zip', 'Staff\Forms\Answers\Uploads\DownloadZipAction')->name('uploads.download_zip')->middleware(['can:staff.forms.answers.export']);
                        Route::get('/export', 'Staff\Forms\Answers\ExportAction')->name('export')->middleware(['can:staff.forms.answers.export']);
                    });

                // 申請フォームエディタ
                Route::prefix('/editor')
                    ->middleware(['can:staff.forms.edit'])
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

                Route::get('/not_answered', 'Staff\Forms\Answers\NotAnswered\ShowAction')->name('not_answered')->middleware(['can:staff.forms.answers.read']);

                Route::get('/preview', 'Staff\Forms\PreviewAction')->name('preview')->middleware(['can:staff.forms.read']);

                // フォームの複製
                Route::post('/copy', 'Staff\Forms\CopyAction')->name('copy')->middleware(['can:staff.forms.duplicate']);;
            });

        Route::prefix('/users')
            ->name('users.')
            ->group(function () {
                Route::get('/', 'Staff\Users\IndexAction')->name('index')->middleware(['can:staff.users.read']);
                Route::get('/api', 'Staff\Users\ApiAction')->name('api')->middleware(['can:staff.users.read']);
                Route::get('/{user}/edit', 'Staff\Users\EditAction')->name('edit')->middleware(['can:staff.users.edit']);
                Route::patch('/{user}', 'Staff\Users\UpdateAction')->name('update')->middleware(['can:staff.users.edit']);
                Route::delete('/{user}', 'Staff\Users\DestroyAction')->name('destroy')->middleware(['can:staff.users.edit']);
                Route::get('/export', 'Staff\Users\ExportAction')->name('export')->middleware(['can:staff.users.export']);

                // 手動本人確認
                Route::patch('/{user}/verify', 'Staff\Users\VerifiedAction')->name('verified')->middleware(['can:staff.users.edit']);
            });

        Route::prefix('/circles')
            ->name('circles.')
            ->group(function () {
                Route::get('/', 'Staff\Circles\IndexAction')->name('index')->middleware(['can:staff.circles.read']);
                Route::get('/api', 'Staff\Circles\ApiAction')->name('api')->middleware(['can:staff.circles.read']);

                // 参加登録設定
                Route::get('/custom_form', 'Staff\Circles\CustomForm\IndexAction')->name('custom_form.index')->middleware(['can:staff.circles.custom_form']);
                Route::post('/custom_form', 'Staff\Circles\CustomForm\StoreAction')->name('custom_form.store')->middleware(['can:staff.circles.custom_form']);
                Route::patch('/custom_form', 'Staff\Circles\CustomForm\UpdateAction')->name('custom_form.update')->middleware(['can:staff.circles.custom_form']);

                // 企画情報編集
                Route::get('/{circle}/edit', 'Staff\Circles\EditAction')->name('edit')->middleware(['can:staff.circles.edit']);
                Route::patch('/{circle}', 'Staff\Circles\UpdateAction')->name('update')->middleware(['can:staff.circles.edit']);
                Route::get('/create', 'Staff\Circles\CreateAction')->name('create')->middleware(['can:staff.circles.edit']);
                Route::post('/', 'Staff\Circles\StoreAction')->name('store')->middleware(['can:staff.circles.edit']);

                // 企画所属者宛のメール送信
                Route::get('/{circle}/email', 'Staff\Circles\SendEmails\IndexAction')->name('email')->middleware(['can:staff.circles.send_email']);
                Route::post('/{circle}/email', 'Staff\Circles\SendEmails\SendAction')->middleware(['can:staff.circles.send_email']);

                // 企画情報エクスポート
                Route::get('/export', 'Staff\Circles\ExportAction')->name('export')->middleware(['can:staff.circles.export']);

                Route::delete('/{circle}', 'Staff\Circles\DestroyAction')->name('destroy')->middleware(['can:staff.circles.delete']);
            });

        Route::prefix('/tags')
            ->name('tags.')
            ->group(function () {
                Route::get('/', 'Staff\Tags\IndexAction')->name('index')->middleware(['can:staff.tags.read']);
                Route::get('/api', 'Staff\Tags\ApiAction')->name('api')->middleware(['can:staff.tags.read']);
                Route::get('/create', 'Staff\Tags\CreateAction')->name('create')->middleware(['can:staff.tags.edit']);
                Route::post('/', 'Staff\Tags\StoreAction')->name('store')->middleware(['can:staff.tags.edit']);
                Route::get('/{tag}/edit', 'Staff\Tags\EditAction')->name('edit')->middleware(['can:staff.tags.edit']);
                Route::patch('/{tag}', 'Staff\Tags\UpdateAction')->name('update')->middleware(['can:staff.tags.edit']);
                Route::get('/{tag}/delete', 'Staff\Tags\DeleteAction')->name('delete')->middleware(['can:staff.tags.delete']);
                Route::delete('/{tag}', 'Staff\Tags\DestroyAction')->name('destroy')->middleware(['can:staff.tags.delete']);
                Route::get('/export', 'Staff\Tags\ExportAction')->name('export')->middleware(['can:staff.tags.export']);
            });

        Route::prefix('/places')
            ->name('places.')
            ->group(function () {
                Route::get('/', 'Staff\Places\IndexAction')->name('index')->middleware(['can:staff.places.read']);
                Route::get('/api', 'Staff\Places\ApiAction')->name('api')->middleware(['can:staff.places.read']);
                Route::get('/create', 'Staff\Places\CreateAction')->name('create')->middleware(['can:staff.places.edit']);
                Route::post('/', 'Staff\Places\StoreAction')->name('store')->middleware(['can:staff.places.edit']);
                Route::get('/{place}/edit', 'Staff\Places\EditAction')->name('edit')->middleware(['can:staff.places.edit']);
                Route::patch('/{place}', 'Staff\Places\UpdateAction')->name('update')->middleware(['can:staff.places.edit']);
                Route::delete('/{place}', 'Staff\Places\DestroyAction')->name('destroy')->middleware(['can:staff.places.delete']);
                Route::get('/export', 'Staff\Places\ExportAction')->name('export')->middleware(['can:staff.places.export']);
            });

        // メール一斉送信
        Route::get('/send_emails', 'Staff\SendEmails\IndexAction')->name('send_emails')->middleware(['can:staff.pages.send_emails']);
        Route::delete('/send_emails', 'Staff\SendEmails\DestroyAction')->middleware(['can:staff.pages.send_emails']);

        Route::prefix('/contacts')
            ->name('contacts.')
            ->group(function () {
                // お問い合わせのメールリスト
                Route::get('/categories', 'Staff\Contacts\Categories\IndexAction')->name('categories.index')->middleware(['can:staff.contacts.categories.read']);
                Route::get('/categories/create', 'Staff\Contacts\Categories\CreateAction')->name('categories.create')->middleware(['can:staff.contacts.categories.edit']);
                Route::post('/categories/create', 'Staff\Contacts\Categories\StoreAction')->middleware(['can:staff.contacts.categories.edit']);
                Route::get('/categories/{category}/edit', 'Staff\Contacts\Categories\EditAction')->name('categories.edit')->middleware(['can:staff.contacts.categories.edit']);
                Route::patch('/categories/{category}', 'Staff\Contacts\Categories\UpdateAction')->name('categories.update')->middleware(['can:staff.contacts.categories.edit']);
                Route::get('/categories/{category}/delete', 'Staff\Contacts\Categories\DeleteAction')->name('categories.delete')->middleware(['can:staff.contacts.categories.delete']);
                Route::delete('/categories/{category}', 'Staff\Contacts\Categories\DestroyAction')->name('categories.destroy')->middleware(['can:staff.contacts.categories.delete']);
            });

        // 配布資料
        Route::prefix('/documents')
            ->name('documents.')
            ->group(function () {
                Route::get('/', 'Staff\Documents\IndexAction')->name('index')->middleware(['can:staff.documents.read']);
                Route::get('/api', 'Staff\Documents\ApiAction')->name('api')->middleware(['can:staff.documents.read']);
                Route::get('/create', 'Staff\Documents\CreateAction')->name('create')->middleware(['can:staff.documents.edit']);
                Route::post('/', 'Staff\Documents\StoreAction')->name('store')->middleware(['can:staff.documents.edit']);
                Route::get('/export', 'Staff\Documents\ExportAction')->name('export')->middleware(['can:staff.documents.export']);
                Route::get('/{document}/edit', 'Staff\Documents\EditAction')->name('edit')->middleware(['can:staff.documents.edit']);
                Route::patch('/{document}', 'Staff\Documents\UpdateAction')->name('update')->middleware(['can:staff.documents.edit']);
                Route::get('/{document}', 'Staff\Documents\ShowAction')->name('show')->middleware(['can:staff.documents.read']);
                Route::delete('/{document}', 'Staff\Documents\DestroyAction')->name('destroy')->middleware(['can:staff.documents.delete']);
            });

        // スタッフの権限設定
        Route::prefix('/permissions')
            ->name('permissions.')
            ->group(function () {
                Route::get('/', 'Staff\Permissions\IndexAction')->name('index')->middleware(['can:staff.permissions.read']);
                Route::get('/api', 'Staff\Permissions\ApiAction')->name('api')->middleware(['can:staff.permissions.read']);
                Route::get('/{user}/edit', 'Staff\Permissions\EditAction')->name('edit')->middleware(['can:staff.permissions.edit']);
                Route::patch('/{user}', 'Staff\Permissions\UpdateAction')->name('update')->middleware(['can:staff.permissions.edit']);
            });
    });

// 管理者ページ（多要素認証も済んでいる状態）
Route::middleware(['auth', 'verified', 'can:admin', 'staffAuthed'])
    ->prefix('/admin')
    ->name('admin.')
    ->group(function () {
        // アクティビティログ
        Route::get('/activity_log', 'Admin\ActivityLog\IndexAction')->name('activity_log.index');
        Route::get('/activity_log/api', 'Admin\ActivityLog\ApiAction')->name('activity_log.api');

        // ポータル情報編集
        Route::get('/portal', 'Admin\Portal\EditAction')->name('portal.edit');
        Route::patch('/portal', 'Admin\Portal\UpdateAction')->name('portal.update');

        // アップデーター
        Route::prefix('/update')
            ->name('update.')
            ->group(function () {
                Route::get('/', 'Admin\Update\IndexAction')->name('index');
                Route::get('/before-update', 'Admin\Update\BeforeUpdateAction')->name('before-update');
                Route::get('/last-step', 'Admin\Update\LastStepAction')->name('last-step');
                Route::post('/run', 'Admin\Update\RunAction')->name('run');
            });
    });
