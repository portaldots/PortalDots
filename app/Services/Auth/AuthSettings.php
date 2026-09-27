<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Eloquents\User;

/**
 * ユーザー登録・ログイン方法・学籍番号・大学提供メールアドレスの有無に関する設定をまとめて読む。
 *
 * プライベートなデプロイ用パッケージが Service Provider から
 * config(['portal.registration.enabled' => false]) のように portal.registration・
 * portal.auth 以下の値を上書きすることで、OSS側のファイルを変更せずに
 * 招待制・メールアドレスのみでのログイン・学籍番号なし・大学提供メールアドレスなしの
 * 運用に対応できる。この設定を参照する箇所は、config() を直接呼ばずにこのクラスを使うこと。
 */
class AuthSettings
{
    /**
     * ユーザー登録を受け付けるかどうか
     */
    public function registrationEnabled(): bool
    {
        return (bool) config('portal.registration.enabled');
    }

    /**
     * ログインIDとして受け付けるカラム名の一覧（'email', 'student_id'）
     *
     * @return string[]
     */
    public function loginIdentifiers(): array
    {
        return config('portal.auth.login_identifiers');
    }

    public function loginByEmailEnabled(): bool
    {
        return in_array('email', $this->loginIdentifiers(), true);
    }

    public function loginByStudentIdEnabled(): bool
    {
        return in_array('student_id', $this->loginIdentifiers(), true);
    }

    /**
     * ログインID欄のラベル・placeholderとして表示する文言
     */
    public function loginIdLabel(): string
    {
        if ($this->loginByStudentIdEnabled() && $this->loginByEmailEnabled()) {
            return config('portal.student_id_name') . 'または連絡先メールアドレス';
        }

        if ($this->loginByStudentIdEnabled()) {
            return config('portal.student_id_name');
        }

        return 'メールアドレス';
    }

    /**
     * 学籍番号の入力・表示・必須化を行うかどうか
     */
    public function studentIdEnabled(): bool
    {
        return (bool) config('portal.auth.student_id');
    }

    /**
     * 大学提供メールアドレスの入力・表示・認証を行うかどうか
     */
    public function univemailEnabled(): bool
    {
        return (bool) config('portal.auth.univemail');
    }

    /**
     * 連絡先メールアドレス（および、有効な場合は大学提供メールアドレス）の
     * メール認証が完了しているかどうか
     */
    public function isVerified(User $user): bool
    {
        if (!$this->univemailEnabled()) {
            return $user->hasVerifiedEmail();
        }

        return $user->hasVerifiedEmail() && $user->hasVerifiedUnivemail();
    }
}
