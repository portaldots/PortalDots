<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Eloquents\User;
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
        bool $is_staff = false,
        bool $is_admin = false
    ) {
        return User::create([
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
    }
}
