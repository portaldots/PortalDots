<?php

declare(strict_types=1);

namespace App\Services\Pages;

use DB;
use App\Eloquents\Page;
use App\Eloquents\User;
use App\Eloquents\Tag;
use App\Services\Emails\SendEmailService;

class PagesService
{
    /**
     * @var SendEmailService
     */
    private $sendEmailService;

    public function __construct(SendEmailService $sendEmailService)
    {
        $this->sendEmailService = $sendEmailService;
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
                'created_by' => $created_by->id,
                'updated_by' => $created_by->id,
                'notes' => $notes,
            ]);

            // 検索時は大文字小文字の区別をしない
            // ($tags と $exist_tags の間で大文字小文字が異なる場合、$exist_tags の表記を優先するため)
            $exist_tags = Tag::select('id')->whereIn('name', $viewable_tags)->get();
            $page->viewableTags()->sync($exist_tags->pluck('id')->all());

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
                'updated_by' => $updated_by->id,
                'notes' => $notes,
            ]);

            // 検索時は大文字小文字の区別をしない
            // ($tags と $exist_tags の間で大文字小文字が異なる場合、$exist_tags の表記を優先するため)
            $exist_tags = Tag::select('id')->whereIn('name', $viewable_tags)->get();
            $page->viewableTags()->sync($exist_tags->pluck('id')->all());

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
