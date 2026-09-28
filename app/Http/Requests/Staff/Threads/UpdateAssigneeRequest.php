<?php

namespace App\Http\Requests\Staff\Threads;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssigneeRequest extends FormRequest
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
            'assignee_id' => 'nullable|integer|exists:users,id',
            'lock_version' => 'required|integer',
        ];
    }
}
