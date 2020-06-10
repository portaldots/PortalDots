<?php

namespace App\Http\Requests\Staff\Documents;

use Illuminate\Foundation\Http\FormRequest;
use App;
use Illuminate\Support\Arr;

class UpdateDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'is_public' => ['required', 'boolean'],
            'is_important' => ['required', 'boolean'],
            'schedule_id' => ['nullable', 'exists:schedules,id'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * バリデーションエラーのカスタム属性の取得
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'name' => '配布資料名',
            'description' => '説明',
            'is_public' => '公開',
            'is_important' => '重要',
            'schedule_id' => 'イベント',
            'notes' => 'スタッフ用メモ',
        ];
    }

    public function messages()
    {
        return [
            'schedule_id.exists' => '指定されたイベントは見つかりません',
        ];
    }
}
