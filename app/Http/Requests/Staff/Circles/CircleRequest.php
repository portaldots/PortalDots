<?php

namespace App\Http\Requests\Staff\Circles;

use Illuminate\Foundation\Http\FormRequest;
use App\Eloquents\User;
use App\Eloquents\Circle;

class CircleRequest extends FormRequest
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
            'name' => Circle::NAME_RULES,
            'name_yomi' => Circle::NAME_YOMI_RULES,
            'group_name' => Circle::GROUP_NAME_RULES,
            'group_name_yomi' => Circle::GROUP_NAME_YOMI_RULES,
            'status' => Circle::STATUS_RULES,
            'leader'    => ['nullable', 'exists:users,student_id'],
            'members'   => ['nullable'],
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
            'name' => '企画の名前',
            'name_yomi' => '企画の名前(よみ)',
            'group_name' => '企画団体の名前',
            'group_name_yomi' => '企画団体の名前(よみ)',
            'leader' => '企画責任者',
            'members' => '学園祭係(副責任者)',
            'status' => '参加登録受理',
        ];
    }

    public function messages()
    {
        return [
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
