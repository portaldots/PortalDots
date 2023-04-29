<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Group extends Model
{
    use HasFactory;
    use LogsActivity;

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
    public const NAME_RULES = ['required', 'string', 'max:255'];
    public const NAME_YOMI_RULES = ['required', 'string', 'max:255', 'regex:/^([ぁ-んァ-ヶー]+)$/u'];

    public function users()
    {
        return $this->belongsToMany(User::class)->using(GroupUser::class)->withPivot('role');
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
