<?php

declare(strict_types=1);

namespace App\Services\Contacts;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Eloquents\Circle;
use App\Eloquents\ContactCategory;
use App\Eloquents\User;
use App\Mail\Contacts\ContactMailable;
use App\Mail\Contacts\EmailCategoryMailable;
use App\Services\Threads\ThreadsService;

class ContactsService
{
    /**
     * @var ThreadsService
     */
    private $threadsService;

    public function __construct(ThreadsService $threadsService)
    {
        $this->threadsService = $threadsService;
    }

    /**
     * お問い合わせを作成する。企画（または企画に所属していないユーザー本人）の会話へ
     * 本文を追記したうえで、既存の確認メール・スタッフ用控えメールを送信する
     *
     * @param Circle|null $circle お問い合わせ対象の企画
     * @param User $sender お問い合わせを作成したユーザー
     * @param string $contactBody お問い合わせ本文
     * @param ContactCategory $category お問い合わせ項目
     * @param \Illuminate\Http\UploadedFile[] $files 添付ファイル
     * @param string|null $clientToken 二重送信防止用のトークン。省略時は毎回新規のお問い合わせになる
     * @return void
     */
    public function create(
        ?Circle $circle,
        User $sender,
        string $contactBody,
        ContactCategory $category,
        array $files = [],
        ?string $clientToken = null
    ) {
        $clientToken = $clientToken ?? (string)Str::uuid();

        $thread = isset($circle)
            ? $this->threadsService->getOrCreateForCircle($circle)
            : $this->threadsService->getOrCreateForUser($sender);

        $entry = $this->threadsService->postCircleMessage(
            $thread,
            $sender,
            $contactBody,
            $category->exists ? $category : null,
            $files,
            $clientToken
        );

        if (!$entry->wasRecentlyCreated) {
            // 同じトークンでの二重送信。メールは初回送信時に送っているため何もしない
            return;
        }

        if (isset($circle) && is_iterable($circle->users) && count($circle->users) > 0) {
            // 企画に所属するユーザー全員に確認メールを送信する
            foreach ($circle->users as $user) {
                $this->send($user, $circle, $sender, $contactBody, $category);
            }
        } else {
            // 企画に所属していないユーザーの場合
            $this->send($sender, null, $sender, $contactBody, $category);
        }

        $this->sendToStaff($circle, $sender, $contactBody, $category);
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
    private function send(
        User $recipient,
        ?Circle $circle,
        User $sender,
        string $contactBody,
        ContactCategory $category
    ) {
        Mail::to($recipient)
            ->send(
                (new ContactMailable($circle, $sender, $contactBody, $category))
                    ->replyTo($category->email, config('portal.admin_name'))
                    ->subject('お問い合わせを承りました')
            );
    }

    /**
     * スタッフ用控えをスタッフに送信する
     *
     * @param Circle|null $circle お問い合わせ対象の企画
     * @param User $sender お問い合わせを作成したユーザー
     * @param string $contactBody お問い合わせ本文
     * @param ContactCategory $category お問い合わせ項目
     * @return void
     */
    private function sendToStaff(?Circle $circle, User $sender, string $contactBody, ContactCategory $category)
    {
        $senderText = isset($circle) ? $circle->name : $sender->name;

        Mail::to($category->email, $category->name)
            ->send(
                (new ContactMailable($circle, $sender, $contactBody, $category))
                    ->replyTo($sender)
                    ->subject("お問い合わせ({$senderText} 様)")
            );
    }
}
