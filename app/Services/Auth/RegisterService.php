<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Eloquents\Group;
use App\Eloquents\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterService
{
    /**
     * ユーザーを作成する
     *
     * @param string $student_id ユーザーのstudent_id
     * @param string $name ユーザーの名前。姓名の間にはスペースを1個以上入れること
     * @param string $name_yomi ユーザーの名前の読み。姓名の間にはスペースを1個以上入れること
     * @param string $email ユーザーの連絡先メールアドレス
     * @param string $univemail_local_part 学校提供メールアドレスのローカルパート
     * @param string $univemail_domain_part 学校提供メールアドレスのドメインパート
     * @param string $tel ユーザーの電話番号
     * @param string $plain_password ハッシュ化していないパスワード
     * @param bool $is_individual 個人として登録するか。団体として登録する場合はfalseにする
     * @param string $group_name 団体名。$is_individualがtrueの場合は必須
     * @param string $group_name_yomi 団体名の読み。$is_individualがtrueの場合は必須
     * @param bool $is_staff スタッフとして登録するか
     * @param bool $is_admin 管理者ユーザーとして登録するか
     * @return User
     */
    public function create(
        string $student_id,
        string $name,
        string $name_yomi,
        string $email,
        string $univemail_local_part,
        string $univemail_domain_part,
        string $tel,
        string $plain_password,
        bool $is_individual = true,
        ?string $group_name = null,
        ?string $group_name_yomi = null,
        bool $is_staff = false,
        bool $is_admin = false
    ): User {
        return DB::transaction(function () use (
            $student_id,
            $name,
            $name_yomi,
            $email,
            $univemail_local_part,
            $univemail_domain_part,
            $tel,
            $plain_password,
            $is_individual,
            $group_name,
            $group_name_yomi,
            $is_staff,
            $is_admin,
        ): User {
            $user = User::create([
                'student_id' => $student_id,
                'name' => $name,
                'name_yomi' => $name_yomi,
                'email' => $email,
                'univemail_local_part' => $univemail_local_part,
                'univemail_domain_part' => $univemail_domain_part,
                'tel' => $tel,
                'password' => Hash::make($plain_password),
                'is_staff' => $is_staff,
                'is_admin' => $is_admin
            ]);

            $group = new Group();
            $group->name = $is_individual ? null : $group_name;
            $group->name_yomi = $is_individual ? null : $group_name_yomi;
            $group->is_individual = $is_individual;
            $group->save();
            $group->users()->attach($user->id, ['role' => 'owner']);

            return $user;
        });
    }
}
