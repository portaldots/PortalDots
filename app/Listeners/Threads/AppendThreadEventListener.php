<?php

declare(strict_types=1);

namespace App\Listeners\Threads;

use App\Eloquents\Circle;
use App\Events\Threads\CircleTimelineEvent;
use App\Services\Threads\ThreadsService;

/**
 * 業務イベントを、対象企画の会話へシステムイベントとして記録する。
 * client_token による重複排除は ThreadsService::appendEvent が行うため、
 * このリスナーが再実行されても会話には1件しか記録されない
 */
class AppendThreadEventListener
{
    private ThreadsService $threadsService;

    public function __construct(ThreadsService $threadsService)
    {
        $this->threadsService = $threadsService;
    }

    public function handle(CircleTimelineEvent $event): void
    {
        $circle = Circle::whereKey($event->circleId())->first();
        if (empty($circle)) {
            return;
        }

        $thread = $this->threadsService->getOrCreateForCircle($circle);

        $this->threadsService->appendEvent(
            $thread,
            $event->threadEventType(),
            $event->threadEventPayload(),
            $event->threadEventClientToken()
        );
    }
}
