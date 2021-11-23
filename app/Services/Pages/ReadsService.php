<?php

declare(strict_types=1);

namespace App\Services\Pages;

use Illuminate\Support\Facades\Auth;
use App\Eloquents\Page;
use App\Eloquents\User;
use App\Services\Circles\SelectorService;

class ReadsService
{
    /**
     * @var int
     */
    private $unreadsCountOnSelectedCircle = 0;

    /**
     * @var SelectorService
     */
    private $selectorService;

    public function __construct(SelectorService $selectorService)
    {
        $this->selectorService = $selectorService;
    }

    /**
     * SelectorServiceにおいて選択中の企画にログインしている場合に表示する
     * お知らせの未読数を取得
     *
     * @return int
     */
    public function getUnreadsCountOnSelectedCircle()
    {
        if (!Auth::check()) {
            $this->unreadsCountOnSelectedCircle = 0;
            return $this->unreadsCountOnSelectedCircle;
        }

        if (!empty($this->unreadsCountOnSelectedCircle)) {
            return $this->unreadsCountOnSelectedCircle;
        }

        $pages = Page::byCircle($this->selectorService->getCircle())
            ->with(['usersWhoRead' => function ($query) {
                $query->where('user_id', Auth::id());
            }])->public()->pinned(false)->get();
        $this->unreadsCountOnSelectedCircle = $pages->reduce(function (int $carry, Page $page) {
            if ($page->usersWhoRead->isEmpty()) {
                return $carry + 1;
            }
            return $carry;
        }, 0);

        return $this->unreadsCountOnSelectedCircle;
    }

    /**
     * 既読としてマークする
     *
     * @param Page $page 既読としてマークする対象のお知らせ
     * @param User $user 既読したユーザー
     * @return void
     */
    public function markAsRead(Page $page, User $user)
    {
        if ($page->usersWhoRead()->where('user_id', $user->id)->doesntExist()) {
            $page->usersWhoRead()->attach($user->id, ['created_at' => now()]);
        }
    }

    /**
     * 指定されたお知らせの既読情報を全て削除する
     *
     * @param Page $page
     * @return void
     */
    public function deleteAllReadsByPage(Page $page)
    {
        $page->usersWhoRead()->detach();
    }
}
