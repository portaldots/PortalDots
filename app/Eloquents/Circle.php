<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Circle extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('circle')
            ->logOnly([
                'id',
                'participation_type_id',
                'name',
                'name_yomi',
                'group_name',
                'group_name_yomi',
                'submitted_at',
                'status',
                'status_reason',
                'status_set_at',
                'notes',
            ])
            ->logOnlyDirty();
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activityArray = $activity->changes()->toArray();

        if (
            $eventName !== 'created' &&
            !empty($activityArray['attributes']['submitted_at']) &&
            empty($activityArray['old']['submitted_at'])
        ) {
            // 企画参加登録を提出した場合、 description を submitted にする。
            $activity->description = 'submitted';
        } else {
            $activity->description = $eventName;
        }
    }

    /**
     * バリデーションルール
     */
    public const NAME_RULES = ['required', 'string', 'max:255'];
    public const NAME_YOMI_RULES = ['required', 'string', 'max:255', 'regex:/^([ぁ-んァ-ヶー]+)$/u'];
    public const GROUP_NAME_RULES = ['required', 'string', 'max:255'];
    public const GROUP_NAME_YOMI_RULES = ['required', 'string', 'max:255', 'regex:/^([ぁ-んァ-ヶー]+)$/u'];
    public const STATUS_RULES = ['required', 'in:pending,approved,rejected'];

    /**
     * ステータスを表す文字列
     */
    // 確認中（DBには pending という値を入れず、null にする）
    public const STATUS_PENDING = 'pending';
    // 受理
    public const STATUS_APPROVED = 'approved';
    // 不受理
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'participation_type_id',
        'name',
        'name_yomi',
        'group_name',
        'group_name_yomi',
        'invitation_token',
        'submitted_at',
        'status',
        'status_reason',
        'status_set_at',
        'status_set_by',
        'notes',
    ];

    protected $dates = [
        'status_set_at',
    ];

    public function participationType()
    {
        return $this->belongsTo(ParticipationType::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class)->using(CircleTag::class);
    }

    public function places()
    {
        return $this->belongsToMany(Place::class, 'booths')->using(Booth::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->using(CircleUser::class)->withPivot('is_leader');
    }

    public function leader()
    {
        return $this->users()->wherePivot('is_leader', true);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function statusSetBy()
    {
        return $this->belongsTo(User::class, 'status_set_by');
    }

    /**
     * メンバーが参加登録に必要な人数だけ集まっており、参加登録の提出が可能かどうか
     *
     * @return boolean
     */
    public function canSubmit()
    {
        return count($this->users) >= $this->participationType->users_count_min
            && count($this->users) <= $this->participationType->users_count_max;
    }

    /**
     * 参加登録が未提出の企画だけに限定するクエリスコープ
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotSubmitted($query)
    {
        return $query->whereNull('submitted_at');
    }

    public function scopeSubmitted($query)
    {
        return $query->whereNotNull('submitted_at');
    }

    public function hasSubmitted()
    {
        return isset($this->submitted_at);
    }

    /**
     * 参加登録は提出されているが、スタッフによるチェックがPendingな企画だけに限定するクエリスコープ
     */
    public function scopePending($query)
    {
        return $query->whereNotNull('submitted_at')->whereNull('status');
    }

    public function isPending()
    {
        return isset($this->submitted_at) && empty($this->status);
    }

    /**
     * スタッフによるチェックがApprovedな企画だけに限定するクエリスコープ
     */
    public function scopeApproved($query)
    {
        return $query->whereNotNull('submitted_at')->where('status', 'approved');
    }

    public function hasApproved()
    {
        return isset($this->submitted_at) && $this->status === 'approved';
    }

    /**
     * スタッフによるチェックがRejectedな企画だけに限定するクエリスコープ
     */
    public function scopeRejected($query)
    {
        return $query->whereNotNull('submitted_at')->where('status', 'rejected');
    }

    public function hasRejected()
    {
        return isset($this->submitted_at) && $this->status === 'rejected';
    }

    /**
     * 企画名(よみ)をひらがなにして保存する
     *
     * @param string $value
     */
    public function setNameYomiAttribute($value)
    {
        // 半角カタカナ・全角カタカナを，全角ひらがなに変換する
        $this->attributes['name_yomi'] = mb_convert_kana($value, 'HVc');
    }

    /**
     * 企画を出店する団体の名称(よみ)をひらがなにして保存する
     *
     * @param string $value
     */
    public function setGroupNameYomiAttribute($value)
    {
        // 半角カタカナ・全角カタカナを，全角ひらがなに変換する
        $this->attributes['group_name_yomi'] = mb_convert_kana($value, 'HVc');
    }
}
