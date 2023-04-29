<?php

namespace App\Http\Requests\Staff\Groups;

use App\Eloquents\Group;
use App\Eloquents\User;
use Illuminate\Foundation\Http\FormRequest;

class GroupRequest extends FormRequest
{
    /**
     * @var User
     */
    private $user;

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
     * バリデーションエラーのカスタム属性の取得
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'name' => '団体名',
            'name_yomi' => '団体名(よみ)',
            'owner' => '責任者',
            'members' => '学園祭係(副責任者)',
            'notes' => 'スタッフ用メモ',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = Group::getValidationRules(isIndividual: false);
        return [
            'name' => $rules['name'],
            'name_yomi' => $rules['name_yomi'],
            'owner' => ['nullable', 'exists:users,student_id'],
            'members' => ['nullable'],
            'notes' => ['nullable'],
        ];
    }

    public function messages()
    {
        return [
            'owner.exists' => 'この' . config('portal.student_id_name') . 'は登録されていません',
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
                $validator->errors()->add('members', '未登録 : ' . implode(' ', $non_registered_member_ids));
            }

            if (!empty($unverified_student_ids)) {
                $validator->errors()->add('members', 'メール未認証 : ' . implode(' ', $unverified_student_ids));
            }
        });
    }
}
