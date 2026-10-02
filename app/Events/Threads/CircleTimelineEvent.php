<?php

declare(strict_types=1);

namespace App\Events\Threads;

/**
 * 企画の会話にシステムイベントとして記録される業務イベントが実装するインターフェース。
 * AppendThreadEventListener はこのインターフェースだけを見て会話へ記録する
 */
interface CircleTimelineEvent
{
    /**
     * イベントの対象企画のID
     */
    public function circleId(): int;

    /**
     * thread_entries.event_type に保存する種別
     */
    public function threadEventType(): string;

    /**
     * thread_entries.event_payload に保存する内容。表示に必要な値をここで
     * 確定させ、後から参照先のレコード名が変わっても表示が崩れないようにする
     */
    public function threadEventPayload(): array;

    /**
     * 同一の事実に対して常に同じ値になるトークン。リスナーの再実行や
     * イベントの重複発行があっても、会話には1件しか記録されない
     */
    public function threadEventClientToken(): string;
}
