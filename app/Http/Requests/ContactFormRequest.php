<?php

namespace App\Http\Requests;

use App\Eloquents\Circle;
use App\Eloquents\ContactCategory;
use App\Eloquents\ThreadEntryAttachment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Request;

class ContactFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->has('circle_id')) {
            return !empty($this->circle_id) && Gate::allows('circle.belongsTo', Circle::find($this->circle_id));
        }
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
            'circle_id' => 'filled',
            'contact_body' => 'required',
            'client_token' => 'nullable|string|max:64',
            'attachments' => 'array|max:' . ThreadEntryAttachment::MAX_FILES,
            'attachments.*' => 'file|max:' . ThreadEntryAttachment::MAX_FILE_SIZE_KB,
        ];
    }

    public function messages()
    {
        return [
            'contact_body.required' => 'お問い合わせ内容は必ず入力してください',
            'attachments.max' => '添付ファイルは' . ThreadEntryAttachment::MAX_FILES . '個までです',
            'attachments.*.max' => '添付ファイルは1つにつき10MBまでです',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!($this->category === '0' || ContactCategory::find($this->category))) {
                $validator->errors()->add('category', 'お問い合わせ項目を選択肢から選んでください');
            }
        });
    }
}
