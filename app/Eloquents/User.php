<?php

namespace App\Eloquents;

use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Eloquents\Circle;
use App\Eloquents\CircleUser;

/**
 * @property string $id
 * @property string $student_id
 * @property string $name
 * @property string $name_yomi
 * @property string $name_family
 * @property string $name_family_yomi
 * @property string $name_given
 * @property string $name_given_yomi
 * @property string $email
 * @property Carbon $email_verified_at
 * @property-read string $univemail
 * @property Carbon $univemail_verified_at
 * @property string $tel
 * @property string $password
 * @property bool $is_staff
 */
class User extends Authenticatable
{
    use Notifiable;

    /**
     * バリデーションルール
     */
    public const STUDENT_ID_RULES = ['required', 'string'];
    // ↓姓と名の間であれば，何個でもスペースを入れてもよしとする
    public const NAME_RULES = ['required', 'string', 'max:255', 'regex:/^([^\s　]+)([\s　]+)([^\s　]+)$/u'];
    // ↓姓と名の間であれば，何個でもスペースを入れてもよしとする
    public const NAME_YOMI_RULES = ['required', 'string', 'max:255', 'regex:/^([ぁ-んァ-ヶー]+)([\s　]+)([ぁ-んァ-ヶー]+)$/u'];
    public const EMAIL_RULES = ['required', 'string', 'email', 'max:255'];
    public const TEL_RULES = ['required', 'string', 'max:255'];
    public const PASSWORD_RULES = ['required', 'string', 'min:8'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'student_id', 'name', 'email', 'tel', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $dates = [
        'email_verified_at',
        'univemail_verified_at',
        'signed_up_at',
    ];

    protected $casts = [
        'is_staff' => 'bool',
        'is_verified_by_staff' => 'bool',
    ];

    public function circles()
    {
        return $this->belongsToMany(Circle::class)->using(CircleUser::class)->withPivot('is_leader');
    }

    /**
     * メール認証が完了しているユーザーだけに限定するクエリスコープ
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at')->whereNotNull('univemail_verified_at');
    }

    /**
     * ログイン ID から該当ユーザーを取得する
     *
     * @param string $login_id
     * @return User
     */
    public function firstByLoginId(string $login_id)
    {
        return $this->where('email', $login_id)
            ->orWhere('student_id', $login_id)
            ->first();
    }

    public function firstByStudentId($student_id)
    {
        return $this->where('student_id', $student_id)->first();
    }

    public function getByStudentIdIn(array $student_ids)
    {
        return $this->whereIn('student_id', $student_ids)->get();
    }

    /**
     * 学籍番号のアルファベットを大文字に変換してセットする(セッター)
     *
     * @param string $value
     */
    public function setStudentIdAttribute($value)
    {
        $this->attributes['student_id'] = mb_strtoupper($value);
    }

    /**
     * 名前を姓と名に分割する
     *
     * @param string $value
     */
    public function setNameAttribute($value)
    {
        // 姓と名を分割する
        $name_array = preg_split('/[\s　]+/u', $value);
        // 姓と名を別カラムへセットする
        $this->attributes['name_family'] = $name_array[0];
        $this->attributes['name_given'] = $name_array[1];
    }

    /**
     * 名前(よみ)を性と名に分割する
     *
     * @param string $value
     */
    public function setNameYomiAttribute($value)
    {
        // 半角カタカナ・全角カタカナを，全角ひらがなに変換する
        $value = mb_convert_kana($value, 'HVc');
        // 姓と名(よみ)を分割する
        $name_array = preg_split('/[\s　]+/u', $value);
        // 姓と名(よみ)を別カラムへセットする
        $this->attributes['name_family_yomi'] = $name_array[0];
        $this->attributes['name_given_yomi'] = $name_array[1];
    }

    /**
     * フルネームを取得する
     *
     * @return string
     */
    public function getNameAttribute()
    {
        return "{$this->name_family} {$this->name_given}";
    }

    /**
     * フルネーム(よみ)を取得する
     *
     * @return string
     */
    public function getNameYomiAttribute()
    {
        return "{$this->name_family_yomi} {$this->name_given_yomi}";
    }

    /**
     * 大学提供メールアドレスを取得する
     *
     * @return string
     */
    public function getUnivemailAttribute()
    {
        return mb_strtolower($this->student_id) . '@' . config('portal.univemail_domain');
    }

    /**
     * email と univemail の両方でメール認証が完了しているかどうか
     *
     * @return bool
     */
    public function areBothEmailsVerified()
    {
        return $this->hasVerifiedEmail() && $this->hasVerifiedUnivemail();
    }

    /**
     * 連絡先メールアドレスのメール認証が完了しているかどうか
     *
     * @return bool
     */
    public function hasVerifiedEmail(): bool
    {
        return (!empty($this->email_verified_at));
    }

    /**
     * 大学提供メールアドレスのメール認証が完了しているかどうか
     *
     * @return bool
     */
    public function hasVerifiedUnivemail(): bool
    {
        return (!empty($this->univemail_verified_at));
    }

    /**
     * 連絡先メールアドレスのメール認証を完了としてマークする
     *
     * @return bool
     */
    public function markEmailAsVerified()
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    /**
     * 大学提供メールアドレスのメール認証を完了としてマークする
     *
     * @return bool
     */
    public function markUnivemailAsVerified()
    {
        return $this->forceFill([
            'univemail_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    public function setSignedUpAt()
    {
        $this->signed_up_at = now();
        return $this->save();
    }

    public function getIsSignedUpAttribute()
    {
        return !empty($this->signed_up_at);
    }
}
