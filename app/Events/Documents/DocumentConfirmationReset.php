<?php

declare(strict_types=1);

namespace App\Events\Documents;

use App\Eloquents\ThreadEntry;
use App\Events\Threads\CircleTimelineEvent;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * 配布資料に新しい版が追加されたことで、企画の確認依頼が確認待ちへ
 * 自動的に戻された
 */
class DocumentConfirmationReset implements ShouldDispatchAfterCommit, CircleTimelineEvent
{
    use Dispatchable;

    public int $circleId;
    public int $documentId;
    public string $documentName;
    public int $version;
    public int $decisionId;

    public function __construct(
        int $circleId,
        int $documentId,
        string $documentName,
        int $version,
        int $decisionId
    ) {
        $this->circleId = $circleId;
        $this->documentId = $documentId;
        $this->documentName = $documentName;
        $this->version = $version;
        $this->decisionId = $decisionId;
    }

    public function circleId(): int
    {
        return $this->circleId;
    }

    public function threadEventType(): string
    {
        return ThreadEntry::EVENT_TYPE_DOCUMENT_CONFIRMATION_RESET;
    }

    public function threadEventPayload(): array
    {
        return [
            'document_id' => $this->documentId,
            'document_name' => $this->documentName,
            'version' => $this->version,
        ];
    }

    public function threadEventClientToken(): string
    {
        return "document-confirmation-reset:{$this->decisionId}";
    }
}
