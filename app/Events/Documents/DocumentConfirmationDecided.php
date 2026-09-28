<?php

declare(strict_types=1);

namespace App\Events\Documents;

use App\Eloquents\ThreadEntry;
use App\Events\Threads\CircleTimelineEvent;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * 企画が配布資料の確認依頼に対して決定した（確認済みにした・修正を依頼した）
 */
class DocumentConfirmationDecided implements ShouldDispatchAfterCommit, CircleTimelineEvent
{
    use Dispatchable;

    public int $circleId;
    public int $documentId;
    public string $documentName;
    public int $version;
    /** @var string DocumentApproval::STATUS_APPROVED または STATUS_CHANGES_REQUESTED */
    public string $decision;
    public ?string $comment;
    public int $decisionId;

    public function __construct(
        int $circleId,
        int $documentId,
        string $documentName,
        int $version,
        string $decision,
        ?string $comment,
        int $decisionId
    ) {
        $this->circleId = $circleId;
        $this->documentId = $documentId;
        $this->documentName = $documentName;
        $this->version = $version;
        $this->decision = $decision;
        $this->comment = $comment;
        $this->decisionId = $decisionId;
    }

    public function circleId(): int
    {
        return $this->circleId;
    }

    public function threadEventType(): string
    {
        return ThreadEntry::EVENT_TYPE_DOCUMENT_CONFIRMATION_DECIDED;
    }

    public function threadEventPayload(): array
    {
        return [
            'document_id' => $this->documentId,
            'document_name' => $this->documentName,
            'version' => $this->version,
            'decision' => $this->decision,
            'comment' => $this->comment,
        ];
    }

    public function threadEventClientToken(): string
    {
        return "document-confirmation-decided:{$this->decisionId}";
    }
}
