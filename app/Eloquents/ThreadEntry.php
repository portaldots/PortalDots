<?php

declare(strict_types=1);

namespace App\Eloquents;

use App\Services\Utils\FormatTextService;
use Illuminate\Database\Eloquent\Model;

/**
 * 会話内の1件のやり取り。メッセージ・内部メモ・システムイベントのいずれか
 *
 * @property int $id
 * @property int $thread_id
 * @property string $kind
 * @property int|null $author_id
 * @property string $author_side
 * @property int|null $contact_category_id
 * @property string|null $body
 * @property string|null $event_type
 * @property array|null $event_payload
 * @property string|null $client_token
 */
class ThreadEntry extends Model
{
    public const KIND_MESSAGE = 'message';
    public const KIND_INTERNAL_NOTE = 'internal_note';
    public const KIND_EVENT = 'event';

    public const AUTHOR_SIDE_STAFF = 'staff';
    public const AUTHOR_SIDE_CIRCLE = 'circle';
    public const AUTHOR_SIDE_SYSTEM = 'system';

    public const EVENT_TYPE_ANSWER_SUBMITTED = 'answer_submitted';
    public const EVENT_TYPE_ANSWER_RETURNED = 'answer_returned';
    public const EVENT_TYPE_ANSWER_ACCEPTED = 'answer_accepted';
    public const EVENT_TYPE_DOCUMENT_CONFIRMATION_REQUESTED = 'document_confirmation_requested';
    public const EVENT_TYPE_DOCUMENT_CONFIRMATION_DECIDED = 'document_confirmation_decided';
    public const EVENT_TYPE_DOCUMENT_CONFIRMATION_RESET = 'document_confirmation_reset';
    public const EVENT_TYPE_FORM_SENT = 'form_sent';
    public const EVENT_TYPE_FORM_DUE_DATE_CHANGED = 'form_due_date_changed';

    protected $fillable = [
        'thread_id',
        'kind',
        'author_id',
        'author_side',
        'contact_category_id',
        'body',
        'event_type',
        'event_payload',
        'client_token',
    ];

    protected $casts = [
        'event_payload' => 'array',
    ];

    public function thread()
    {
        return $this->belongsTo(Thread::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function contactCategory()
    {
        return $this->belongsTo(ContactCategory::class);
    }

    public function attachments()
    {
        return $this->hasMany(ThreadEntryAttachment::class);
    }

    /**
     * イベントの内容を短い1行のテキストにする。circle側・staff側で共通の文言を使う
     *
     * @return string
     */
    public function eventText(): string
    {
        $payload = $this->event_payload ?? [];

        switch ($this->event_type) {
            case self::EVENT_TYPE_ANSWER_SUBMITTED:
                return ($payload['submitted_by_staff'] ?? false)
                    ? "{$payload['form_name']}をスタッフが修正しました（第{$payload['revision']}版）"
                    : "{$payload['form_name']}を提出しました（第{$payload['revision']}版）";
            case self::EVENT_TYPE_ANSWER_RETURNED:
                return "{$payload['form_name']}を差し戻しました : {$payload['reason']}";
            case self::EVENT_TYPE_ANSWER_ACCEPTED:
                return "{$payload['form_name']}を完了にしました";
            case self::EVENT_TYPE_DOCUMENT_CONFIRMATION_REQUESTED:
                return "{$payload['document_name']} 第{$payload['version']}版の確認を依頼しました";
            case self::EVENT_TYPE_DOCUMENT_CONFIRMATION_DECIDED:
                if (($payload['decision'] ?? null) === DocumentApproval::STATUS_APPROVED) {
                    return "{$payload['document_name']} 第{$payload['version']}版を確認済みにしました";
                }
                return "{$payload['document_name']} 第{$payload['version']}版の修正を依頼しました : {$payload['comment']}";
            case self::EVENT_TYPE_DOCUMENT_CONFIRMATION_RESET:
                return "{$payload['document_name']} 第{$payload['version']}版が追加されたため、確認待ちに戻りました";
            case self::EVENT_TYPE_FORM_SENT:
                $dueAt = $this->formatDueAt($payload['due_at'] ?? null);
                return "申請 {$payload['form_name']} が送付されました（期限 : {$dueAt}）";
            case self::EVENT_TYPE_FORM_DUE_DATE_CHANGED:
                $dueAt = $this->formatDueAt($payload['due_at'] ?? null);
                return "申請 {$payload['form_name']} の期限が変更されました（期限 : {$dueAt}）";
            default:
                return $this->event_type ?? '';
        }
    }

    private function formatDueAt(?string $dueAt): string
    {
        if (empty($dueAt)) {
            return '指定なし';
        }
        return FormatTextService::datetime($dueAt);
    }
}
