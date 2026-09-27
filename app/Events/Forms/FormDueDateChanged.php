<?php

declare(strict_types=1);

namespace App\Events\Forms;

use App\Eloquents\ThreadEntry;
use App\Events\Threads\CircleTimelineEvent;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * 送付済みフォームの、企画ごとの期限が変更された
 */
class FormDueDateChanged implements ShouldDispatchAfterCommit, CircleTimelineEvent
{
    use Dispatchable;

    public int $circleId;
    public int $formId;
    public string $formName;
    public ?string $dueAt;
    public int $assignmentId;

    public function __construct(
        int $circleId,
        int $formId,
        string $formName,
        ?string $dueAt,
        int $assignmentId
    ) {
        $this->circleId = $circleId;
        $this->formId = $formId;
        $this->formName = $formName;
        $this->dueAt = $dueAt;
        $this->assignmentId = $assignmentId;
    }

    public function circleId(): int
    {
        return $this->circleId;
    }

    public function threadEventType(): string
    {
        return ThreadEntry::EVENT_TYPE_FORM_DUE_DATE_CHANGED;
    }

    public function threadEventPayload(): array
    {
        return [
            'form_id' => $this->formId,
            'form_name' => $this->formName,
            'due_at' => $this->dueAt,
        ];
    }

    public function threadEventClientToken(): string
    {
        // 同じ期限への変更（再送信や再試行）は同じトークンになり、重複を作らない
        return "form-due-date-changed:{$this->assignmentId}:" . ($this->dueAt ?? 'none');
    }
}
