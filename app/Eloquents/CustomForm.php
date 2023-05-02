<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class CustomForm extends Model
{
    protected $fillable = [
        'type',
        'form_id',
    ];

    protected $appends = ['form_name'];

    private static $noCacheForm = false;

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function getFormNameAttribute(): string
    {
        return self::getAllowedTypesDict()[$this->type];
    }

    /**
     * getFormByType を実行する際、キャッシュせず常にDBからFormを取得するようにする
     *
     * 主にテストケースを書く際に利用する。テスト以外では原則として利用しない
     *
     * @return void
     */
    public static function noCacheForm()
    {
        static::$noCacheForm = true;
    }

    public static function getFormByType(string $type): ?Form
    {
        static $forms = [];

        if (empty(self::getAllowedTypesDict()[$type])) {
            throw new InvalidArgumentException();
        }

        if (empty($forms[$type]) || static::$noCacheForm) {
            $custom_form = self::where('type', $type)->first();
            if (!empty($custom_form)) {
                $forms[$type] = $custom_form->form()->first();
            } else {
                // キャッシュ後にカスタムフォームが削除されると、
                // $noCacheForm を true にしてもキャッシュが返されてしまう
                // 問題があったため、ここで明示的に null を代入する
                $forms[$type] = null;
            }
        }

        return $forms[$type] ?? null;
    }
}
