<?php

namespace App\Http\Requests\Staff\Threads;

use App\Eloquents\ThreadEntryAttachment;
use Illuminate\Foundation\Http\FormRequest;

class PostNoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // 権限確認はルートの can ミドルウェアで行っている
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
            'body' => 'required|string',
            'client_token' => 'required|string|max:64',
            'attachments' => 'array|max:' . ThreadEntryAttachment::MAX_FILES,
            'attachments.*' => 'file|max:' . ThreadEntryAttachment::MAX_FILE_SIZE_KB,
        ];
    }
}
