<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Circle extends Model
{
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

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
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

    public function getCustomFormAnswer()
    {
        $form = CustomForm::getFormByType('circle');
        if (empty($form)) {
            return null;
        }
        return $form->answers()->where('circle_id', $this->id)->first();
    }
}
