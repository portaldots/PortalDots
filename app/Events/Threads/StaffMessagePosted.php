<?php

declare(strict_types=1);

namespace App\Events\Threads;

use App\Eloquents\Thread;
use App\Eloquents\ThreadEntry;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * スタッフが会話にメッセージを投稿した。企画への通知メールの送信のみに使う
 * （会話への記録自体は ThreadsService::postStaffMessage が直接行うため、
 * このイベントは CircleTimelineEvent を実装しない）
 */
class StaffMessagePosted implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public Thread $thread;
    public ThreadEntry $entry;

    public function __construct(Thread $thread, ThreadEntry $entry)
    {
        $this->thread = $thread;
        $this->entry = $entry;
    }
}
