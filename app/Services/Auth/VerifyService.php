<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Eloquents\User;

class VerifyService
{
    /**
     * 連絡先メールアドレスの認証を完了としてマークする
     *
     * @param  User  $user
     * @return bool
     */
    public function markEmailAsVerified(User $user, $email): bool
    {
        if ($user->hasVerifiedEmail()) {
            return false;
        }
        if ($user->email !== $email) {
            return false;
        }
        return $user->markEmailAsVerified();
    }

    /**
     * 大学提供メールアドレスの認証を完了としてマークする
     *
     * @param  User  $user
     * @return bool
     */
    public function markUnivemailAsVerified(User $user, $email): bool
    {
        if ($user->hasVerifiedUnivemail()) {
            return false;
        }
        if ($user->univemail !== $email) {
            return false;
        }
        return $user->markUnivemailAsVerified();
    }
}
