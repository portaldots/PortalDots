<?php

declare(strict_types=1);

namespace App\Services\Circles\ValueObjects;

use App\Eloquents\Answer;
use App\Eloquents\DocumentApproval;
use App\Eloquents\Form;
use Carbon\Carbon;

/**
 * 企画の進捗を構成する1件の単位
 *
 * 申請フォームへの回答1件、または配布資料の確認依頼1件を表す。
 * CircleProgressService でのみ生成する。
 */
final class ProgressUnit
{
    public const TYPE_FORM = 'form';
    public const TYPE_DOCUMENT = 'document';

    public const STATE_TODO = 'todo';
    public const STATE_REVIEW = 'review';
    public const STATE_CHANGES = 'changes';
    public const STATE_DONE = 'done';

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $state;

    /**
     * @var string
     */
    private $label;

    /**
     * @var Carbon|null 申請フォームの実効的な期限（配布資料の確認依頼では null）
     */
    private $dueAt;

    /**
     * @var bool
     */
    private $overdue;

    /**
     * @var Form|null
     */
    private $form;

    /**
     * @var Answer|null
     */
    private $answer;

    /**
     * @var DocumentApproval|null
     */
    private $documentApproval;

    public function __construct(
        string $type,
        string $state,
        string $label,
        ?Carbon $dueAt,
        bool $overdue,
        ?Form $form,
        ?Answer $answer,
        ?DocumentApproval $documentApproval
    ) {
        $this->type = $type;
        $this->state = $state;
        $this->label = $label;
        $this->dueAt = $dueAt;
        $this->overdue = $overdue;
        $this->form = $form;
        $this->answer = $answer;
        $this->documentApproval = $documentApproval;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isType(string $type): bool
    {
        return $this->type === $type;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function isState(string $state): bool
    {
        return $this->state === $state;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getDueAt(): ?Carbon
    {
        return $this->dueAt;
    }

    public function isOverdue(): bool
    {
        return $this->overdue;
    }

    public function getForm(): ?Form
    {
        return $this->form;
    }

    public function getAnswer(): ?Answer
    {
        return $this->answer;
    }

    public function getDocumentApproval(): ?DocumentApproval
    {
        return $this->documentApproval;
    }
}
