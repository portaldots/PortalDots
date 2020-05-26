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

    public function setCircle(Circle $circle)
    {
        session([self::SESSION_KEY_CIRCLE_ID => $circle->id]);
    }

    public function getCircle()
    {
        return Circle::find(session(self::SESSION_KEY_CIRCLE_ID, null)) ?? null;
    }
}
