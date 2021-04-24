<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Eloquents\User;
use App\Notifications\Auth\StaffAuthNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * スタッフ認証機能を提供するサービス
 *
 * スタッフモードにアクセスするには、メールでの本人確認が必要。
 */
final class StaffAuthService
{
    private const SESSION_KEY_USER_ID = 'staff_auth_service__user_id';
    private const SESSION_KEY_VERIFY_CODE_HASH = 'staff_auth_service__verify_code_hash';
    private const SESSION_KEY_EXPIRED_AT = 'staff_auth_service__expired_at';

    public const SESSION_KEY_STAFF_AUTHORIZED = 'staff_authorized';

    /**
     * ユーザーへスタッフ認証のためのメールを送信
     *
     * @param User $user
     * @return void
     */
    public function send(User $user)
    {
        $verify_code = $this->setVerifyCode($user);
        $user->notify(new StaffAuthNotification($user, $verify_code));
    }

    /**
     * スタッフ認証のコードを生成し、セッションに保存
     *
     * @param User $user
     * @return string $verify_code 認証コード
     */
    private function setVerifyCode(User $user)
    {
        $verify_code = (string)random_int(1000000, 9999999);
        session([
            self::SESSION_KEY_USER_ID => $user->id,
            self::SESSION_KEY_VERIFY_CODE_HASH => Hash::make($verify_code),
            self::SESSION_KEY_EXPIRED_AT => Carbon::now()->addMinutes(5)->format('Y-m-d H:i:s'),
        ]);
        return $verify_code;
    }

    /**
     * スタッフ認証コードが正しければスタッフモードへのアクセスを許可
     *
     * @param User $user
     * @param string $verify_code
     * @return bool 認証結果
     */
    public function verifyAndAuthenticate(User $user, string $verify_code)
    {
        $user_id = session(self::SESSION_KEY_USER_ID);
        $verify_code_hash = session(self::SESSION_KEY_VERIFY_CODE_HASH);
        $expired_at = new Carbon(session(self::SESSION_KEY_EXPIRED_AT));

        $this->forget();

        $result = isset($user_id) && isset($verify_code_hash) && isset($expired_at) &&
            $user->id === $user_id &&
            Hash::check($verify_code, $verify_code_hash) &&
            Carbon::now()->lte($expired_at);

        if ($result) {
            session([self::SESSION_KEY_STAFF_AUTHORIZED => true]);
            return true;
        }

        return false;
    }

    /**
     * StaffAuthService で扱うセッションを全てリセット
     *
     * @return void
     */
    public function forget()
    {
        session([
            self::SESSION_KEY_EXPIRED_AT => null,
            self::SESSION_KEY_STAFF_AUTHORIZED => null,
            self::SESSION_KEY_USER_ID => null,
            self::SESSION_KEY_VERIFY_CODE_HASH => null,
        ]);
    }
}
