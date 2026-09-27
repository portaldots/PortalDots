<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property integer $id
 */
class Answer extends Model
{
    use LogsActivity;

    public const REVIEW_STATUS_SUBMITTED = 'submitted';
    public const REVIEW_STATUS_RETURNED = 'returned';
    public const REVIEW_STATUS_ACCEPTED = 'accepted';

    // lock_version はDB上デフォルト0だが、Eloquentはinsert時にDBのデフォルト値を
    // 読み返さないため、作成直後のインスタンスでも0になるよう明示する
    protected $attributes = [
        'lock_version' => 0,
    ];

    protected $fillable = [
        'form_id',
        'circle_id',
        'review_status',
        'review_note',
        'reviewed_by',
        'reviewed_at',
        'submitted_at',
        'lock_version',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'submitted_at' => 'datetime',
        'lock_version' => 'int',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('answer')
            ->logOnly([
                'id',
                'circle.id',
                'circle.name',
                'form.id',
                'form.name',
            ])
            ->logOnlyDirty();
    }

    public function details()
    {
        return $this->hasMany(AnswerDetail::class);
    }

    public function revisions()
    {
        return $this->hasMany(AnswerRevision::class);
    }

    public function circle()
    {
        return $this->belongsTo(Circle::class);
    }

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
