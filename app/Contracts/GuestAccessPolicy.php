<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * 未ログインの利用者がホーム・お知らせ・配布資料へアクセスできるかどうかを制限するための拡張ポイント
 */
interface GuestAccessPolicy
{
    /**
     * 未ログインの利用者のアクセスを許可するかどうか
     *
     * @return bool
     */
    public function allowsGuests(): bool;
}
