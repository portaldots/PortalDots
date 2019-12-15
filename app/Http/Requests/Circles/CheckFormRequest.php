<?php

namespace App\Http\Requests\Circles;

use Illuminate\Foundation\Http\FormRequest;
use App\Eloquents\User;

class CheckFormRequest extends FormRequest
{
    public function __construct(User $user)
    {
        $this->user = $user;
    }

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
            'name'      => ['required', 'max:255'],
            'leader'    => ['nullable', 'exists:users,student_id'],
            'members'   => ['nullable'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => '団体名は必ず入力してください',
            'name.unique'   => 'すでに存在する団体名です',
            'name.max'      => '255文字以下で入力してください',
            'leader.exists' => 'この学籍番号は登録されていません',
        ];
    }

    public function withValidator($validator)
    {
        $unverified_student_ids = [];

        $non_registered_member_ids = str_replace(["\r\n", "\r", "\n"], "\n", $this->members);
        $non_registered_member_ids = explode("\n", $non_registered_member_ids);
        $non_registered_member_ids = array_filter($non_registered_member_ids, "strlen");

        $members = $this->user->getByStudentIdIn($non_registered_member_ids);

        foreach ($members as $member) {
            $non_registered_member_ids = array_diff($non_registered_member_ids, [$member->student_id]);
            if (!$member->areBothEmailsVerified()) {
                $unverified_student_ids[] = $member->student_id;
            }
        }
        $validator->after(function ($validator) use ($non_registered_member_ids, $unverified_student_ids) {
            if (!empty($non_registered_member_ids)) {
                $validator->errors()->add('members', '未登録：' . implode(' ', $non_registered_member_ids));
            }

            if (!empty($unverified_student_ids)) {
                $validator->errors()->add('members', 'メール未認証：' . implode(' ', $unverified_student_ids));
            }
        });
    }
}
