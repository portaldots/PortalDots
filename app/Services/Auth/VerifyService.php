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
    public function markEmailAsVerified(User $user): bool
    {
        if ($user->hasVerifiedEmail()) {
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
    public function markUnivemailAsVerified(User $user): bool
    {
        if ($user->hasVerifiedUnivemail()) {
            return false;
        }
        return $user->markUnivemailAsVerified();
    }
}
