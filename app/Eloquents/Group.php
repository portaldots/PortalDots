<?php

namespace App\Eloquents;

use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Group extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'name',
        'name_yomi',
        'is_individual',
        'notes'
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('group')
            ->logOnly([
                'id',
                'name',
                'name_yomi',
                'is_individual',
                'notes',
            ])
            ->logOnlyDirty();
    }

    /**
     * バリデーションルール
     */
    public static function getValidationRules(bool $isIndividual)
    {
        return [
            'name' => [Rule::excludeIf($isIndividual), 'required', 'string', 'max:255'],
            'name_yomi' => [Rule::excludeIf($isIndividual), 'required', 'string', 'max:255', 'regex:/^([ぁ-んァ-ヶー]+)$/u'],
        ];
    }

    /**
     * 個人団体ではない通常の団体のみを取得するためのスコープ。
     */
    public function scopeNormal($query)
    {
        return $query->where('is_individual', false);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->using(GroupUser::class)->withPivot('role');
    }

    public function owner()
    {
        return $this->users()->wherePivot('role', 'owner');
    }

    public function circles()
    {
        return $this->hasMany(Circle::class);
    }

    /**
     * 団体名(よみ)をひらがなにして保存する
     *
     * @param string $value
     */
    public function setNameYomiAttribute($value)
    {
        // 半角カタカナ・全角カタカナを，全角ひらがなに変換する
        $this->attributes['name_yomi'] = mb_convert_kana($value, 'HVc');
    }
}
