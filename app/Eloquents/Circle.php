<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Circle extends Model
{
    /**
     * バリデーションルール
     */
    public const NAME_RULES = ['filled', 'string', 'max:255'];
    public const NAME_YOMI_RULES = ['filled', 'string', 'max:255', 'regex:/^([ぁ-んァ-ヶー]+)$/u'];
    public const GROUP_NAME_RULES = ['filled', 'string', 'max:255'];
    public const GROUP_NAME_YOMI_RULES = ['filled', 'string', 'max:255', 'regex:/^([ぁ-んァ-ヶー]+)$/u'];

    protected $fillable = [
        'name',
        'name_yomi',
        'group_name',
        'group_name_yomi',
        'invitation_token',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('approved', function (Builder $builder) {
            $builder->whereNotNull('submitted_at')->where('status', 'approved');
        });
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->using(CircleUser::class)->withPivot('is_leader');
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    /**
     * メンバーが参加登録に必要な人数だけ集まっており、参加登録の提出が可能かどうか
     *
     * @return boolean
     */
    public function canSubmit()
    {
        return count($this->users) >= config('portal.users_number_to_submit_circle');
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

    public function hasSubmitted()
    {
        return isset($this->submitted_at);
    }

    /**
     * 参加登録は提出されているが、スタッフによるチェックがPendingな企画だけに限定するクエリスコープ
     */
    public function scopePending()
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
    public function scopeApproved()
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
    public function scopeRejected()
    {
        return $query->whereNotNull('submitted_at')->where('status', 'rejected');
    }

    public function hasRejected()
    {
        return isset($this->submitted_at) && $this->status === 'rejected';
    }

    /**
     * 企画の名前(よみ)をひらがなにして保存する
     *
     * @param string $value
     */
    public function setNameYomiAttribute($value)
    {
        // 半角カタカナ・全角カタカナを，全角ひらがなに変換する
        $this->attributes['name_yomi'] = mb_convert_kana($value, 'HVc');
    }

    /**
     * 企画団体の名前(よみ)をひらがなにして保存する
     *
     * @param string $value
     */
    public function setGroupNameYomiAttribute($value)
    {
        // 半角カタカナ・全角カタカナを，全角ひらがなに変換する
        $this->attributes['group_name_yomi'] = mb_convert_kana($value, 'HVc');
    }
}
