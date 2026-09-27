<?php

declare(strict_types=1);

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;

/**
 * 配布資料の版を、企画に個別に確認してもらうよう依頼した記録
 *
 * @property int $id
 * @property int $document_id
 * @property int $circle_id
 * @property int $document_version_id
 * @property string $status
 * @property int|null $requested_by
 * @property int $lock_version
 */
class DocumentApproval extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_CHANGES_REQUESTED = 'changes_requested';
    public const STATUS_APPROVED = 'approved';

    /**
     * 企画向けの状態ラベル
     */
    public const CIRCLE_STATUS_LABELS = [
        self::STATUS_PENDING => '確認してください',
        self::STATUS_CHANGES_REQUESTED => '修正対応中',
        self::STATUS_APPROVED => '確認済み',
    ];

    /**
     * スタッフ向けの状態ラベル
     */
    public const STAFF_STATUS_LABELS = [
        self::STATUS_PENDING => '確認待ち',
        self::STATUS_CHANGES_REQUESTED => '修正依頼あり',
        self::STATUS_APPROVED => '確認済み',
    ];

    // lock_version はDB上デフォルト0だが、Eloquentはinsert時にDBのデフォルト値を
    // 読み返さないため、作成直後のインスタンスでも0になるよう明示する
    protected $attributes = [
        'lock_version' => 0,
    ];

    protected $fillable = [
        'document_id',
        'circle_id',
        'document_version_id',
        'status',
        'requested_by',
        'lock_version',
    ];

    protected $casts = [
        'lock_version' => 'int',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function circle()
    {
        return $this->belongsTo(Circle::class);
    }

    public function documentVersion()
    {
        return $this->belongsTo(DocumentVersion::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function decisions()
    {
        return $this->hasMany(DocumentApprovalDecision::class)->orderBy('created_at', 'desc');
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
