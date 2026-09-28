<?php

namespace App\Exceptions\Threads;

use App\Eloquents\Thread;
use RuntimeException;

/**
 * lock_version が現在のDB上の値と一致しない場合、つまり別の操作によって
 * 会話がすでに更新されている場合にthrowされる
 */
class StaleThreadException extends RuntimeException
{
    public Thread $thread;

    public function __construct(Thread $thread)
    {
        parent::__construct('会話が別の操作によって更新されているため、この操作を行えません。');
        $this->thread = $thread;
    }
}
