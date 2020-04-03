<?php

declare(strict_types=1);

namespace App\Services\Forms;

use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Eloquents\Form;

class ValidationRulesService
{
    /**
     * バリデーションルール配列を取得する
     *
     * 回答の下書き保存用の簡易バリデーションとしたい場合、$isStrict 引数の値を false にする
     *
     * @param Form $form
     * @param Request $request
     * @param bool $isStrict 厳密なバリデーションルールにするか
     * @return void
     */
    public function getRulesFromForm(Form $form, Request $request, bool $isStrict = true)
    {
        $questions = $form->questions;

        $rules = [];

        foreach ($questions as $question) {
            $rule = [];

            // 回答必須かどうか
            if ($question->is_required && $isStrict) {
                $rule[] = 'required';
            } else {
                $rule[] = 'nullable';
            }

            // 型チェック
            if (in_array($question->type, ['text', 'textarea'], true)) {
                $rule[] = 'string';
            } elseif ($question->type === 'checkbox') {
                $rule[] = 'array';
            } elseif ($question->type === 'number') {
                $rule[] = 'integer';
            } elseif ($question->type === 'upload') {
                $rule[] = 'file';
            }

            // 文字数・整数値・ファイルサイズ範囲制限
            if (
                in_array($question->type, ['text', 'textarea', 'number', 'checkbox'], true) &&
                isset($question->number_min) && $isStrict
            ) {
                // upload に対しては、最小ファイルサイズを検証しない
                $rule[] = 'min:' . $question->number_min;
            }

            if (
                in_array($question->type, ['text', 'textarea', 'number', 'checkbox', 'upload'], true) &&
                isset($question->number_max) && $isStrict
            ) {
                $rule[] = 'max:' . $question->number_max;
            }

            // ファイルの種類チェック
            if ($question->type === 'upload') {
                $rule[] = 'mimes:' . \implode(',', $question->allowed_types_array);
            }

            // ファイルアップロード設問において、回答が "__KEEP__" だった場合、
            // 以前アップロードしたファイルを回答として利用したいということなので、
            // 回答のバリデーションは行わない
            if (
                $question->type === 'upload' && isset($request->answers[$question->id]) &&
                $request->answers[$question->id] === '__KEEP__'
            ) {
                $rule = ['required'];
            }

            $rules['answers.' . $question->id] = $rule;

            // 用意された選択肢の中から選択されているか
            if (in_array($question->type, ['radio', 'select', 'checkbox'], true)) {
                $rules['answers.' . $question->id . '.*'] = Rule::in($question->options_array);
            }
        }

        return $rules;
    }

    public function getAttributesFromForm(Form $form)
    {
        return $form->questions->mapWithKeys(function ($question) {
            return ['answers.' . $question->id => $question->name];
        });
    }
}
