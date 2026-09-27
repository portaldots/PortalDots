<?php

declare(strict_types=1);

namespace App\Events\Forms;

use App\Eloquents\ThreadEntry;
use App\Events\Threads\CircleTimelineEvent;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * requires_review なフォームで、回答のリビジョンが提出された
 * （企画側の新規提出・再提出、またはスタッフによる修正のいずれか）
 */
class AnswerSubmitted implements ShouldDispatchAfterCommit, CircleTimelineEvent
{
    use Dispatchable;

    public int $circleId;
    public int $formId;
    public string $formName;
    public int $answerId;
    public int $revision;
    public bool $submittedByStaff;

    public function __construct(
        int $circleId,
        int $formId,
        string $formName,
        int $answerId,
        int $revision,
        bool $submittedByStaff
    ) {
        $this->circleId = $circleId;
        $this->formId = $formId;
        $this->formName = $formName;
        $this->answerId = $answerId;
        $this->revision = $revision;
        $this->submittedByStaff = $submittedByStaff;
    }

    public function circleId(): int
    {
        return $this->circleId;
    }

    public function threadEventType(): string
    {
        return ThreadEntry::EVENT_TYPE_ANSWER_SUBMITTED;
    }

    public function threadEventPayload(): array
    {
        return [
            'form_id' => $this->formId,
            'form_name' => $this->formName,
            'answer_id' => $this->answerId,
            'revision' => $this->revision,
            'submitted_by_staff' => $this->submittedByStaff,
        ];
    }

    public function threadEventClientToken(): string
    {
        return "answer-submitted:{$this->answerId}:{$this->revision}";
    }
}
