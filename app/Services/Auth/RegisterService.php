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
     * @param string $student_id ユーザーの学籍番号
     * @param string $name ユーザーの名前。姓名の間にはスペースを1個以上入れること
     * @param string $name_yomi ユーザーの名前の読み。姓名の間にはスペースを1個以上入れること
     * @param string $email ユーザーの連絡先メールアドレス
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
            'tel' => $tel,
            'password' => Hash::make($plain_password),
            'is_staff' => $is_staff,
            'is_admin' => $is_admin
        ]);
    }
}
