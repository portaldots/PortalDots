<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Eloquents\User;
use App\Mail\Auth\EmailVerificationMailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

class EmailService
{
    /**
     * 連絡先メールアドレスと大学提供メールアドレスの両方に確認メールを送信する
     *
     * @param  User  $user
     */
    public function sendAll(User $user)
    {
        $this->sendToUnivemail($user);
        $this->sendToEmail($user);
    }

    /**
     * 連絡先メールアドレス宛に登録確認メールを送信する
     *
     * @param  User  $user
     */
    public function sendToEmail(User $user)
    {
        if ($user->hasVerifiedEmail()) {
            return;
        }

        $verifyUrl = $this->generateSignedUrl($user, 'email');
        $is_edit = $user->is_signed_up;
        $this->send($user->email, $user->name, $verifyUrl, $is_edit);
    }

    /**
     * 大学提供メールアドレス宛に登録確認メールを送信する
     *
     * @param  User  $user
     */
    public function sendToUnivemail(User $user)
    {
        if ($user->hasVerifiedUnivemail()) {
            return;
        }

        $verifyUrl = $this->generateSignedUrl($user, 'univemail');
        $is_edit = $user->is_signed_up;
        $this->send($user->univemail, $user->name, $verifyUrl, $is_edit);
    }

    /**
     * メール送信共通処理
     *
     * @param  string  $email
     * @param  string  $name
     * @param  string  $verifyUrl
     */
    private function send(string $email, string $name, string $verifyUrl, bool $is_edit = false)
    {
        $recipient = new \stdClass();
        $recipient->email = $email;
        $recipient->name = $name;

        Mail::to($recipient)
        ->send(new EmailVerificationMailable($verifyUrl, $name, $is_edit));
    }

    /**
     * メール認証用URLを発行する
     *
     * @param  User  $user
     * @param  string  $type  email か univemail
     * @return string
     */
    private function generateSignedUrl(User $user, string $type)
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'type' => $type,
                'user' => $user->id,
                'email' => ($type === 'univemail' ? $user->univemail : $user->email )
            ]
        );
    }
}
