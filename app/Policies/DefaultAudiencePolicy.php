<?php

declare(strict_types=1);

namespace App\Policies;

use App\Contracts\AudiencePolicy;

/**
 * OSS版における AudiencePolicy の初期実装。すべての公開範囲を許可する。
 */
class DefaultAudiencePolicy implements AudiencePolicy
{
    public function allowedAudiences(): array
    {
        return [
            self::EVERYONE,
            self::SIGNED_IN,
            self::SELECTED,
        ];
    }
}
