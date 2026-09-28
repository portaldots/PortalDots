<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Eloquents\Thread;
use App\Eloquents\User;

/**
 * 会話についての通知メールで使う返信先 (Reply-To) を差し替えるための拡張ポイント。
 * 返信メールを受信して会話に自動で追記するデプロイ向けに、会話を特定できる
 * アドレス（例: 署名付きトークンを含む reply+<token>@replies.example.com）を
 * 返せるようにする
 */
interface ThreadReplyAddress
{
    /**
     * 会話 $thread について $recipient 宛てに送るメールで使う返信先アドレスを返す。
     * nullの場合は既定の返信先（お問い合わせ用メールアドレス）を使う
     */
    public function for(Thread $thread, User $recipient): ?string;
}
