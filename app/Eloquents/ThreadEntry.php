<?php

declare(strict_types=1);

namespace App\Eloquents;

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
}
