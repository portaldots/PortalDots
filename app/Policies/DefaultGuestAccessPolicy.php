<?php

declare(strict_types=1);

namespace App\Policies;

use App\Contracts\GuestAccessPolicy;

/**
 * OSS版における GuestAccessPolicy の初期実装。未ログインの利用者のアクセスを許可する。
 */
class DefaultGuestAccessPolicy implements GuestAccessPolicy
{
    public function allowsGuests(): bool
    {
        return true;
    }
}
