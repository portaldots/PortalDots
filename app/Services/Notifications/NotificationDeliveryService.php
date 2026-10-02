<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * 通知メールの重複送信を防ぐ。$dedupeKey ごとに1回だけ $send を実行する
 */
class NotificationDeliveryService
{
    /**
     * $dedupeKey を予約してから $send を実行する。
     *
     * 既に予約済み（過去に送信済み、または送信中）であれば何もしない。
     * $send が例外を投げた場合は予約を取り消し、再試行で送信できるようにする
     *
     * @param string $dedupeKey 同一の事実に対して常に同じ値になるトークン
     * @param int|null $recipientUserId
     * @param callable $send
     * @return void
     */
    public function sendOnce(string $dedupeKey, ?int $recipientUserId, callable $send): void
    {
        $reserved = DB::table('notification_deliveries')->insertOrIgnore([
            'dedupe_key' => $dedupeKey,
            'recipient_user_id' => $recipientUserId,
            'created_at' => now(),
        ]);

        if ($reserved === 0) {
            return;
        }

        try {
            $send();
        } catch (Throwable $e) {
            DB::table('notification_deliveries')->where('dedupe_key', $dedupeKey)->delete();
            throw $e;
        }
    }
}
