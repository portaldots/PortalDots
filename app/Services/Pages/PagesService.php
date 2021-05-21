<?php

declare(strict_types=1);

namespace App\Services\Pages;

use App\Eloquents\Page;
use App\Eloquents\User;
use App\Eloquents\Tag;
use App\Services\Emails\SendEmailService;
use App\Services\Utils\ActivityLogService;
use Illuminate\Support\Facades\DB;

class PagesService
{
    /**
     * @var SendEmailService
     */
    private $sendEmailService;

    /**
     * @var ActivityLogService
     */
    private $activityLogService;

    public function __construct(
        SendEmailService $sendEmailService,
        ActivityLogService $activityLogService
    ) {
        $this->sendEmailService = $sendEmailService;
        $this->activityLogService = $activityLogService;
    }

    /**
     * お知らせを作成する
     *
     * @param string $title タイトル
     * @param string $body 本文
     * @param User $created_by 作成者
     * @param string $notes スタッフ用メモ
     * @param array|null $viewable_tags お知らせを閲覧可能な企画のタグ
     * @return Page
     */
    public function createPage(
        string $title,
        string $body,
        User $created_by,
        string $notes,
        ?array $viewable_tags = null
    ): Page {
        return DB::transaction(function () use ($title, $body, $created_by, $notes, $viewable_tags) {
            $page = Page::create([
                'title' => $title,
                'body' => $body,
                'notes' => $notes,
            ]);

            // 検索時は大文字小文字の区別をしない
            // ($tags と $exist_tags の間で大文字小文字が異なる場合、$exist_tags の表記を優先するため)
            $exist_tags = Tag::select('id', 'name')->whereIn('name', $viewable_tags)->get();
            $page->viewableTags()->sync($exist_tags->pluck('id')->all());

            // ログに残す
            $map_function = function ($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                ];
            };

            $this->activityLogService->logOnlyAttributesChanged(
                'page_viewable_tag',
                $created_by,
                $page,
                [],
                $exist_tags->map($map_function)->toArray()
            );

            return $page;
        });
    }

    /**
     * お知らせを更新する
     *
     * @param Page $page 更新するお知らせ
     * @param string $title タイトル
     * @param string $body 本文
     * @param User $updated_by 更新者
     * @param string $notes スタッフ用メモ
     * @param array|null $viewable_tags お知らせを閲覧可能な企画のタグ
     * @return bool
     */
    public function updatePage(
        Page $page,
        string $title,
        string $body,
        User $updated_by,
        string $notes,
        ?array $viewable_tags = null
    ): bool {
        return DB::transaction(function () use ($page, $title, $body, $updated_by, $notes, $viewable_tags) {
            $page->update([
                'title' => $title,
                'body' => $body,
                'notes' => $notes,
            ]);

            $old_tags = $page->viewableTags()->orderBy('id')->get();

            // 検索時は大文字小文字の区別をしない
            // ($tags と $exist_tags の間で大文字小文字が異なる場合、$exist_tags の表記を優先するため)
            $exist_tags = Tag::select('id', 'name')->whereIn('name', $viewable_tags)->orderBy('id')->get();
            $page->viewableTags()->sync($exist_tags->pluck('id')->all());

            // ログに残す
            $map_function = function ($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                ];
            };

            $this->activityLogService->logOnlyAttributesChanged(
                'page_viewable_tag',
                $updated_by,
                $page,
                $old_tags->map($map_function)->toArray(),
                $exist_tags->map($map_function)->toArray()
            );

            return true;
        });
    }

    public function removePage(Page $page)
    {
        return DB::transaction(function () use ($page) {
            $page->viewableTags()->detach();
            return $page->delete();
        });
    }

    /**
     * お知らせにおいて指定されているタグに所属している企画のユーザーへ、
     * メール送信予約を行う
     *
     * @param Page $page
     */
    public function sendEmailsByPage(Page $page)
    {
        $page->refresh();
        $users = User::verified()->byTags(
            $page->viewableTags()->select('tags.id')->get()
        )->get();
        $this->sendEmailService->bulkEnqueue(
            $page->title,
            $page->body,
            $users
        );
    }
}
