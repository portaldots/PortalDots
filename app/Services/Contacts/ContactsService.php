<?php

declare(strict_types=1);

namespace App\Services\Contacts;

use Illuminate\Support\Facades\Mail;
use App\Eloquents\Circle;
use App\Eloquents\User;
use App\Mail\Contacts\ContactMailable;

class ContactsService
{
    /**
     * お問い合わせを作成する
     *
     * @param Circle|null $circle お問い合わせ対象の企画
     * @param User $sender お問い合わせを作成したユーザー
     * @param string $contactBody お問い合わせ本文
     * @return bool
     */
    public function create(?Circle $circle, User $sender, string $contactBody)
    {
        if (isset($circle) && is_iterable($circle->users) && count($circle->users) > 0) {
            // 企画に所属するユーザー全員に確認メールを送信する
            foreach ($circle->users as $user) {
                $this->send($user, $circle, $sender, $contactBody);
            }
        } else {
            // 企画に所属していないユーザーの場合
            $this->send($sender, null, $sender, $contactBody);
        }

        $this->sendToStaff($circle, $sender, $contactBody);
    }

    /**
     * メールを送信する
     *
     * @param User $recipient メールを送信する宛先
     * @param Circle|null $circle お問い合わせ対象の企画
     * @param User $sender お問い合わせを作成したユーザー
     * @param string $contactBody お問い合わせ本文
     * @return void
     */
    private function send(User $recipient, ?Circle $circle, User $sender, string $contactBody)
    {
        Mail::to($recipient)
            ->send(
                (new ContactMailable($circle, $sender, $contactBody))
                    ->replyTo(config('portal.contact_email'), config('portal.admin_name'))
                    ->subject('お問い合わせを承りました')
            );
    }

    /**
     * スタッフ用控えをスタッフに送信する
     *
     * @param Circle|null $circle お問い合わせ対象の企画
     * @param User $sender お問い合わせを作成したユーザー
     * @param string $contactBody お問い合わせ本文
     * @return void
     */
    private function sendToStaff(?Circle $circle, User $sender, string $contactBody)
    {
        $senderText = isset($circle) ? $circle->name : $sender->name;

        Mail::to(config('portal.contact_email'), config('portal.admin_name'))
            ->send(
                (new ContactMailable($circle, $sender, $contactBody))
                    ->replyTo($sender)
                    ->subject("お問い合わせ({$senderText} 様)")
            );
    }
}
