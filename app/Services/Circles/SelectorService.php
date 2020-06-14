<?php

declare(strict_types=1);

namespace App\Services\Circles;

use App\Eloquents\Circle;
use App\Eloquents\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * ログイン先の企画を変更・取得するためのサービス
 */
class SelectorService
{
    private const SESSION_KEY_CIRCLE_ID = 'selector_service__circle_id';

    /**
     * @var array|null
     */
    private $selectableCircles = null;

    /**
     * @var Circle|null
     */
    private $circle = null;

    /**
     * ログイン先の企画を選択する画面において、ユーザーが選択可能な企画の一覧を取得
     *
     * @param User $user
     * @return Collection
     */
    public function getSelectableCirclesList(User $user): Collection
    {
        if (empty($this->selectableCircles[$user->id])) {
            $this->selectableCircles[$user->id] = $user->circles()->approved()->get();
        }
        return $this->selectableCircles[$user->id];
    }

    /**
     * CircleSelectorDropdown.vue コンポーネントの circles prop で利用可能な値を取得
     *
     * @param User $user
     * @param string $redirect_to 企画をセットしたあとにリダイレクトする先のURL
     * @return string
     */
    public function getJsonForCircleSelectorDropdown(User $user, string $redirect_to): string
    {
        $circles = $this->getSelectableCirclesList($user);
        return $circles->map(function (Circle $circle) use ($redirect_to) {
            return [
                'id' => $circle->id,
                'name' => $circle->name,
                'group_name' => $circle->group_name,
                'href' => route('circles.selector.set', ['redirect_to' => $redirect_to, 'circle' => $circle]),
            ];
        })->toJson();
    }

    public function setCircle(?Circle $circle = null)
    {
        if (empty($circle)) {
            return;
        }
        session([self::SESSION_KEY_CIRCLE_ID => $circle->id]);
    }

    public function getCircle()
    {
        $circle_id = session(self::SESSION_KEY_CIRCLE_ID, null);

        if (empty($circle_id)) {
            // キャッシュを削除した上で null を返す
            $this->circle = null;
            return null;
        }

        if (empty($this->circle)) {
            $this->circle = Circle::find($circle_id) ?? null;
        }

        if (!$this->circle->hasApproved()) {
            // 企画にログイン後、スタッフによってステータスが「受理」以外に
            // 変更されることも想定される。そのため、ログイン中の企画の
            // ステータスが「受理」になっているかを確認し、
            // 「受理」以外であれば企画へのログイン状態を解除する。
            $this->reset();
            $this->circle = null;
        }

        return $this->circle;
    }

    public function reset()
    {
        session([self::SESSION_KEY_CIRCLE_ID => null]);
    }
}
