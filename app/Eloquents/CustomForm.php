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

    /**
     * type カラムに入りうる文字列
     *
     * @return array
     */
    public static function getAllowedTypesDict()
    {
        // 将来的に翻訳関数をかませる可能性があるので、
        // 定数ではなく関数とした
        return [
            'circle' => '企画参加登録',
            'user' => 'ユーザー登録',
        ];
    }

    /**
     * カスタム対象のフォームに常設の設問
     *
     * @return array
     */
    public static function getPermanentQuestionsDict()
    {
        // 将来的に翻訳関数をかませる可能性があるので、
        // 定数ではなく関数とした
        $dict = [
            'circle' => [
                [
                    'id' => 'circle.name',
                    'name' => '企画の名前',
                    'type' => 'text',
                    'is_required' => true,
                ],
                [
                    'id' => 'circle.name_yomi',
                    'name' => '企画の名前(よみ)',
                    'type' => 'text',
                    'is_required' => true,
                ],
                [
                    'id' => 'circle.group_name',
                    'name' => '企画団体の名前',
                    'type' => 'text',
                    'is_required' => true,
                ],
                [
                    'id' => 'circle.group_name_yomi',
                    'name' => '企画団体の名前(よみ)',
                    'type' => 'text',
                    'is_required' => true,
                ],
            ]
        ];

        return array_map(function ($questions) {
            return array_map(function ($question) {
                return \array_merge($question, ['is_permanent' => true]);
            }, $questions);
        }, $dict);
    }

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
                $forms[$type] = $custom_form->form()->withoutGlobalScope('withoutCustomForms')->first();
            }
        }

        return $forms[$type] ?? null;
    }
}
