<?php

declare(strict_types=1);

namespace App\Events\Forms;

use App\Eloquents\ThreadEntry;
use App\Events\Threads\CircleTimelineEvent;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * フォームが企画へ個別に送付された
 */
class FormSent implements ShouldDispatchAfterCommit, CircleTimelineEvent
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
        return ThreadEntry::EVENT_TYPE_FORM_SENT;
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
        return "form-sent:{$this->assignmentId}";
    }
}
