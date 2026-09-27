<?php

declare(strict_types=1);

namespace App\Events\Forms;

use App\Eloquents\ThreadEntry;
use App\Events\Threads\CircleTimelineEvent;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * requires_review なフォームの回答が、スタッフにより差し戻された
 */
class AnswerReturned implements ShouldDispatchAfterCommit, CircleTimelineEvent
{
    use Dispatchable;

    public int $circleId;
    public int $formId;
    public string $formName;
    public int $answerId;
    public string $reason;
    public int $lockVersion;

    /**
     * @param int $lockVersion 差し戻し後のAnswer::lock_version。同じ回答への
     *  複数回の差し戻しをそれぞれ別の記録として残すために使う
     */
    public function __construct(
        int $circleId,
        int $formId,
        string $formName,
        int $answerId,
        string $reason,
        int $lockVersion
    ) {
        $this->circleId = $circleId;
        $this->formId = $formId;
        $this->formName = $formName;
        $this->answerId = $answerId;
        $this->reason = $reason;
        $this->lockVersion = $lockVersion;
    }

    public function circleId(): int
    {
        return $this->circleId;
    }

    public function threadEventType(): string
    {
        return ThreadEntry::EVENT_TYPE_ANSWER_RETURNED;
    }

    public function threadEventPayload(): array
    {
        return [
            'form_id' => $this->formId,
            'form_name' => $this->formName,
            'answer_id' => $this->answerId,
            'reason' => $this->reason,
        ];
    }

    public function threadEventClientToken(): string
    {
        return "answer-returned:{$this->answerId}:{$this->lockVersion}";
    }
}
