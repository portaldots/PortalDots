<?php

declare(strict_types=1);

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;

/**
 * 確認依頼に対する決定・状態リセットの履歴。追記のみで、過去の版に対する
 * 決定も参照できるようにする
 *
 * @property int $id
 * @property int $document_approval_id
 * @property int $document_version_id
 * @property string $status
 * @property string|null $comment
 * @property int|null $decided_by
 */
class DocumentApprovalDecision extends Model
{
    protected $fillable = [
        'document_approval_id',
        'document_version_id',
        'status',
        'comment',
        'decided_by',
    ];

    public function documentApproval()
    {
        return $this->belongsTo(DocumentApproval::class);
    }

    public function documentVersion()
    {
        return $this->belongsTo(DocumentVersion::class);
    }

    public function decidedBy()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function circleStatusLabel(): string
    {
        return DocumentApproval::CIRCLE_STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function staffStatusLabel(): string
    {
        return DocumentApproval::STAFF_STATUS_LABELS[$this->status] ?? $this->status;
    }
}
