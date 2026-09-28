<?php

declare(strict_types=1);

namespace App\Events\Forms;

use App\Eloquents\ThreadEntry;
use App\Events\Threads\CircleTimelineEvent;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * requires_review なフォームの回答が、スタッフにより完了にされた
 */
class AnswerAccepted implements ShouldDispatchAfterCommit, CircleTimelineEvent
{
    use Dispatchable;

    public int $circleId;
    public int $formId;
    public string $formName;
    public int $answerId;
    public int $lockVersion;

    /**
     * @param int $lockVersion 完了後のAnswer::lock_version。同じ回答が一度差し戻された後
     *  再び完了になった場合も、それぞれ別の記録として残すために使う
     */
    public function __construct(
        int $circleId,
        int $formId,
        string $formName,
        int $answerId,
        int $lockVersion
    ) {
        $this->circleId = $circleId;
        $this->formId = $formId;
        $this->formName = $formName;
        $this->answerId = $answerId;
        $this->lockVersion = $lockVersion;
    }

    public function circleId(): int
    {
        return $this->circleId;
    }

    public function threadEventType(): string
    {
        return ThreadEntry::EVENT_TYPE_ANSWER_ACCEPTED;
    }

    public function threadEventPayload(): array
    {
        return [
            'form_id' => $this->formId,
            'form_name' => $this->formName,
            'answer_id' => $this->answerId,
        ];
    }

    public function threadEventClientToken(): string
    {
        return "answer-accepted:{$this->answerId}:{$this->lockVersion}";
    }
}
