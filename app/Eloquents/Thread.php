<?php

declare(strict_types=1);

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;

/**
 * 企画ごと、または企画に所属していないユーザーごとに1つ存在する会話
 *
 * @property int $id
 * @property int|null $circle_id
 * @property int|null $user_id
 * @property string $status
 * @property int|null $assignee_id
 * @property \Illuminate\Support\Carbon|null $last_entry_at
 * @property int $lock_version
 */
class Thread extends Model
{
    public const STATUS_NEEDS_STAFF = 'needs_staff';
    public const STATUS_AWAITING_REPLY = 'awaiting_reply';
    public const STATUS_RESOLVED = 'resolved';

    /**
     * 企画向けの状態ラベル
     */
    public const CIRCLE_STATUS_LABELS = [
        self::STATUS_NEEDS_STAFF => '対応中',
        self::STATUS_AWAITING_REPLY => 'ご返信をお待ちしています',
        self::STATUS_RESOLVED => '解決済み',
    ];

    /**
     * スタッフ向けの状態ラベル
     */
    public const STAFF_STATUS_LABELS = [
        self::STATUS_NEEDS_STAFF => '対応が必要',
        self::STATUS_AWAITING_REPLY => '返答待ち',
        self::STATUS_RESOLVED => '解決済み',
    ];

    // lock_version はDB上デフォルト0だが、Eloquentはinsert時にDBのデフォルト値を
    // 読み返さないため、作成直後のインスタンスでも0になるよう明示する
    protected $attributes = [
        'lock_version' => 0,
    ];

    protected $fillable = [
        'circle_id',
        'user_id',
        'status',
        'assignee_id',
        'last_entry_at',
        'lock_version',
    ];

    protected $casts = [
        'last_entry_at' => 'datetime',
        'lock_version' => 'int',
    ];

    public function circle()
    {
        return $this->belongsTo(Circle::class);
    }

    /**
     * 企画に所属していないユーザーがお問い合わせを送った場合の、会話の持ち主
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function entries()
    {
        return $this->hasMany(ThreadEntry::class)->orderBy('created_at')->orderBy('id');
    }

    public function circleStatusLabel(): string
    {
        return self::CIRCLE_STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function staffStatusLabel(): string
    {
        return self::STAFF_STATUS_LABELS[$this->status] ?? $this->status;
    }
}
