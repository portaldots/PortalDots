<?php

declare(strict_types=1);

namespace App\Services\Threads;

use App\Eloquents\Answer;
use App\Eloquents\AnswerRevision;
use App\Eloquents\Circle;
use App\Eloquents\Document;
use App\Eloquents\DocumentApproval;
use App\Eloquents\Form;
use App\Eloquents\ThreadEntry;
use App\Eloquents\User;

/**
 * 会話に表示するシステムイベントから、関連ページへのリンク先を求める。
 * 参照先が削除されている・すでに閲覧できない場合は null を返し、
 * 呼び出し側（Blade）はリンクなしでテキストのみ表示する
 */
class ThreadEventLinkService
{
    /**
     * circle側から見たリンク先
     *
     * @return array{route: string, params: array}|null
     */
    public static function circleLink(ThreadEntry $entry, Circle $circle, ?User $user): ?array
    {
        $payload = $entry->event_payload ?? [];

        switch ($entry->event_type) {
            case ThreadEntry::EVENT_TYPE_ANSWER_SUBMITTED:
                return self::answerRevisionLink($payload, false);
            case ThreadEntry::EVENT_TYPE_ANSWER_RETURNED:
            case ThreadEntry::EVENT_TYPE_ANSWER_ACCEPTED:
                return self::answerEditLink($payload, false);
            case ThreadEntry::EVENT_TYPE_DOCUMENT_CONFIRMATION_REQUESTED:
            case ThreadEntry::EVENT_TYPE_DOCUMENT_CONFIRMATION_DECIDED:
            case ThreadEntry::EVENT_TYPE_DOCUMENT_CONFIRMATION_RESET:
                return self::documentApprovalLink($payload, $circle, $user);
            case ThreadEntry::EVENT_TYPE_FORM_SENT:
            case ThreadEntry::EVENT_TYPE_FORM_DUE_DATE_CHANGED:
                return self::formAnswerLink($payload, $circle);
            default:
                return null;
        }
    }

    /**
     * staff側から見たリンク先
     *
     * @return array{route: string, params: array}|null
     */
    public static function staffLink(ThreadEntry $entry): ?array
    {
        $payload = $entry->event_payload ?? [];

        switch ($entry->event_type) {
            case ThreadEntry::EVENT_TYPE_ANSWER_SUBMITTED:
                return self::answerRevisionLink($payload, true);
            case ThreadEntry::EVENT_TYPE_ANSWER_RETURNED:
            case ThreadEntry::EVENT_TYPE_ANSWER_ACCEPTED:
                return self::answerEditLink($payload, true);
            case ThreadEntry::EVENT_TYPE_DOCUMENT_CONFIRMATION_REQUESTED:
            case ThreadEntry::EVENT_TYPE_DOCUMENT_CONFIRMATION_DECIDED:
            case ThreadEntry::EVENT_TYPE_DOCUMENT_CONFIRMATION_RESET:
                return self::staffDocumentLink($payload);
            case ThreadEntry::EVENT_TYPE_FORM_SENT:
            case ThreadEntry::EVENT_TYPE_FORM_DUE_DATE_CHANGED:
                return self::staffFormAnswersLink($payload);
            default:
                return null;
        }
    }

    /**
     * @return array{route: string, params: array}|null
     */
    private static function answerRevisionLink(array $payload, bool $isStaff): ?array
    {
        $form = Form::find($payload['form_id'] ?? null);
        $answer = Answer::find($payload['answer_id'] ?? null);
        if (empty($form) || empty($answer) || (int)$answer->form_id !== (int)$form->id || !$form->requires_review) {
            return null;
        }
        if (!$isStaff && !$form->is_public) {
            return null;
        }

        $revision = AnswerRevision::where('answer_id', $answer->id)
            ->where('revision', $payload['revision'] ?? null)
            ->first();
        if (empty($revision)) {
            return null;
        }

        return [
            'route' => $isStaff ? 'staff.forms.answers.revisions.show' : 'forms.answers.revisions.show',
            'params' => ['form' => $form->id, 'answer' => $answer->id, 'revision' => $revision->revision],
        ];
    }

    /**
     * @return array{route: string, params: array}|null
     */
    private static function answerEditLink(array $payload, bool $isStaff): ?array
    {
        $form = Form::find($payload['form_id'] ?? null);
        $answer = Answer::find($payload['answer_id'] ?? null);
        if (empty($form) || empty($answer) || (int)$answer->form_id !== (int)$form->id) {
            return null;
        }
        if (!$isStaff && !$form->is_public) {
            return null;
        }

        return [
            'route' => $isStaff ? 'staff.forms.answers.edit' : 'forms.answers.edit',
            'params' => ['form' => $form->id, 'answer' => $answer->id],
        ];
    }

    /**
     * @return array{route: string, params: array}|null
     */
    private static function documentApprovalLink(array $payload, Circle $circle, ?User $user): ?array
    {
        $document = Document::whereKey($payload['document_id'] ?? null)
            ->visibleTo($user, $circle)
            ->first();
        if (empty($document)) {
            return null;
        }

        $approvalExists = DocumentApproval::where('document_id', $document->id)
            ->where('circle_id', $circle->id)
            ->exists();
        if (!$approvalExists) {
            return null;
        }

        return [
            'route' => 'documents.approval.show',
            'params' => ['document' => $document->id],
        ];
    }

    /**
     * @return array{route: string, params: array}|null
     */
    private static function staffDocumentLink(array $payload): ?array
    {
        $document = Document::find($payload['document_id'] ?? null);
        if (empty($document)) {
            return null;
        }

        return [
            'route' => 'staff.documents.edit',
            'params' => ['document' => $document->id],
        ];
    }

    /**
     * 企画側の、その申請への回答ページ（未回答ならこれから回答するページ）
     *
     * @return array{route: string, params: array}|null
     */
    private static function formAnswerLink(array $payload, Circle $circle): ?array
    {
        $form = Form::find($payload['form_id'] ?? null);
        if (empty($form) || !$form->is_public) {
            return null;
        }

        $answer = Answer::where('form_id', $form->id)->where('circle_id', $circle->id)->first();
        if (!empty($answer)) {
            return [
                'route' => 'forms.answers.edit',
                'params' => ['form' => $form->id, 'answer' => $answer->id],
            ];
        }

        return [
            'route' => 'forms.answers.create',
            'params' => ['form' => $form->id],
        ];
    }

    /**
     * @return array{route: string, params: array}|null
     */
    private static function staffFormAnswersLink(array $payload): ?array
    {
        $form = Form::find($payload['form_id'] ?? null);
        if (empty($form)) {
            return null;
        }

        return [
            'route' => 'staff.forms.answers.index',
            'params' => ['form' => $form->id],
        ];
    }
}
