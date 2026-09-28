<?php

declare(strict_types=1);

namespace App\Policies;

use App\Contracts\ThreadReplyAddress;
use App\Eloquents\Thread;
use App\Eloquents\User;

/**
 * OSS版における ThreadReplyAddress の初期実装。常に既定の返信先を使う。
 */
class DefaultThreadReplyAddress implements ThreadReplyAddress
{
    public function for(Thread $thread, User $recipient): ?string
    {
        return null;
    }
}
