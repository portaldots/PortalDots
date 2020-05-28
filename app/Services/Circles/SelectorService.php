<?php

declare(strict_types=1);

namespace App\Services\Circles;

use App\Eloquents\Circle;

/**
 * ログイン先の企画を変更・取得するためのサービス
 */
class SelectorService
{
    private const SESSION_KEY_CIRCLE_ID = 'selector_service__circle_id';

    /**
     * @var Circle|null
     */
    private $circle = null;

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

        return $this->circle;
    }

    public function reset()
    {
        session([self::SESSION_KEY_CIRCLE_ID => null]);
    }
}
