<?php

declare(strict_types=1);

namespace App\Services\Threads;

use App\Contracts\FileStorageLayout;
use App\Eloquents\Circle;
use App\Eloquents\ContactCategory;
use App\Eloquents\Thread;
use App\Eloquents\ThreadEntry;
use App\Eloquents\User;
use App\Events\Threads\StaffMessagePosted;
use App\Exceptions\Threads\StaleThreadException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ThreadsService
{
    private FileStorageLayout $fileStorageLayout;

    public function __construct(FileStorageLayout $fileStorageLayout)
    {
        $this->fileStorageLayout = $fileStorageLayout;
    }

    /**
     * 企画の会話を取得する。まだ存在しない場合は作成する（常時ONだが、初回利用時に遅延作成する）
     *
     * @param Circle $circle
     * @return Thread
     */
    public function getOrCreateForCircle(Circle $circle): Thread
    {
        return DB::transaction(function () use ($circle) {
            Circle::whereKey($circle->id)->lockForUpdate()->firstOrFail();
            $thread = Thread::where('circle_id', $circle->id)->lockForUpdate()->first();
            if (!empty($thread)) {
                return $thread;
            }

            return Thread::create([
                'circle_id' => $circle->id,
                'user_id' => null,
                'status' => Thread::STATUS_RESOLVED,
            ]);
        });
    }

    /**
     * 企画に所属していないユーザー本人の会話を取得する。まだ存在しない場合は作成する
     *
     * @param User $user
     * @return Thread
     */
    public function getOrCreateForUser(User $user): Thread
    {
        return DB::transaction(function () use ($user) {
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $thread = Thread::whereNull('circle_id')->where('user_id', $user->id)->lockForUpdate()->first();
            if (!empty($thread)) {
                return $thread;
            }

            return Thread::create([
                'circle_id' => null,
                'user_id' => $user->id,
                'status' => Thread::STATUS_RESOLVED,
            ]);
        });
    }

    /**
     * 企画側からメッセージを送信する。常に needs_staff にする
     *
     * @param Thread $thread
     * @param User $author
     * @param string $body
     * @param ContactCategory|null $category
     * @param UploadedFile[] $files
     * @param string $clientToken 二重送信防止用のトークン。同じトークンでの再送信は
     *  新しいエントリを作らず、既存のエントリをそのまま返す（$entry->wasRecentlyCreated で判別できる）
     * @return ThreadEntry
     */
    public function postCircleMessage(
        Thread $thread,
        User $author,
        string $body,
        ?ContactCategory $category,
        array $files,
        string $clientToken
    ): ThreadEntry {
        return DB::transaction(function () use ($thread, $author, $body, $category, $files, $clientToken) {
            $thread = Thread::whereKey($thread->id)->lockForUpdate()->firstOrFail();

            $existing = $thread->entries()->where('client_token', $clientToken)->first();
            if (!empty($existing)) {
                return $existing;
            }

            $entry = $thread->entries()->create([
                'kind' => ThreadEntry::KIND_MESSAGE,
                'author_id' => $author->id,
                'author_side' => ThreadEntry::AUTHOR_SIDE_CIRCLE,
                'contact_category_id' => optional($category)->id,
                'body' => $body,
                'client_token' => $clientToken,
            ]);
            $this->storeAttachments($entry, $files);

            $thread->update([
                'status' => Thread::STATUS_NEEDS_STAFF,
                'last_entry_at' => $entry->created_at,
                'lock_version' => $thread->lock_version + 1,
            ]);

            return $entry;
        });
    }

    /**
     * スタッフからメッセージを送信する
     *
     * @param Thread $thread
     * @param User $author
     * @param string $body
     * @param string $targetStatus Thread::STATUS_AWAITING_REPLY または Thread::STATUS_RESOLVED
     * @param UploadedFile[] $files
     * @param string $clientToken
     * @return ThreadEntry
     */
    public function postStaffMessage(
        Thread $thread,
        User $author,
        string $body,
        string $targetStatus,
        array $files,
        string $clientToken
    ): ThreadEntry {
        return DB::transaction(function () use ($thread, $author, $body, $targetStatus, $files, $clientToken) {
            $thread = Thread::whereKey($thread->id)->lockForUpdate()->firstOrFail();

            $existing = $thread->entries()->where('client_token', $clientToken)->first();
            if (!empty($existing)) {
                return $existing;
            }

            $entry = $thread->entries()->create([
                'kind' => ThreadEntry::KIND_MESSAGE,
                'author_id' => $author->id,
                'author_side' => ThreadEntry::AUTHOR_SIDE_STAFF,
                'body' => $body,
                'client_token' => $clientToken,
            ]);
            $wasRecentlyCreated = $entry->wasRecentlyCreated;
            $this->storeAttachments($entry, $files);

            $thread->update([
                'status' => $targetStatus,
                'last_entry_at' => $entry->created_at,
                'lock_version' => $thread->lock_version + 1,
            ]);

            if ($wasRecentlyCreated) {
                event(new StaffMessagePosted($thread, $entry));
            }

            return $entry;
        });
    }

    /**
     * スタッフが内部メモを書く。会話の状態は変更しない
     *
     * @param Thread $thread
     * @param User $author
     * @param string $body
     * @param UploadedFile[] $files
     * @param string $clientToken
     * @return ThreadEntry
     */
    public function postInternalNote(
        Thread $thread,
        User $author,
        string $body,
        array $files,
        string $clientToken
    ): ThreadEntry {
        return DB::transaction(function () use ($thread, $author, $body, $files, $clientToken) {
            $thread = Thread::whereKey($thread->id)->lockForUpdate()->firstOrFail();

            $existing = $thread->entries()->where('client_token', $clientToken)->first();
            if (!empty($existing)) {
                return $existing;
            }

            $entry = $thread->entries()->create([
                'kind' => ThreadEntry::KIND_INTERNAL_NOTE,
                'author_id' => $author->id,
                'author_side' => ThreadEntry::AUTHOR_SIDE_STAFF,
                'body' => $body,
                'client_token' => $clientToken,
            ]);
            $this->storeAttachments($entry, $files);

            $thread->update([
                'last_entry_at' => $entry->created_at,
                'lock_version' => $thread->lock_version + 1,
            ]);

            return $entry;
        });
    }

    /**
     * 担当者を設定する
     *
     * @param Thread $thread
     * @param User|null $assignee
     * @param int $expectedLockVersion 画面表示時に読み込んだ lock_version。現在のDB上の値と
     *  一致しない場合は StaleThreadException をthrowする
     * @return Thread
     */
    public function setAssignee(Thread $thread, ?User $assignee, int $expectedLockVersion): Thread
    {
        return DB::transaction(function () use ($thread, $assignee, $expectedLockVersion) {
            $thread = Thread::whereKey($thread->id)->lockForUpdate()->firstOrFail();

            if ($thread->lock_version !== $expectedLockVersion) {
                throw new StaleThreadException($thread);
            }

            $thread->update([
                'assignee_id' => optional($assignee)->id,
                'lock_version' => $thread->lock_version + 1,
            ]);

            return $thread;
        });
    }

    /**
     * システムイベントの記録を追加する。業務イベントのリスナーから呼び出される
     *
     * @param Thread $thread
     * @param string $eventType
     * @param array|null $payload
     * @param string $clientToken 同じ事実に対して常に同じ値を渡す。同じトークンでの
     *  再記録は新しいエントリを作らず、既存のエントリをそのまま返す
     * @return ThreadEntry
     */
    public function appendEvent(Thread $thread, string $eventType, ?array $payload, string $clientToken): ThreadEntry
    {
        return DB::transaction(function () use ($thread, $eventType, $payload, $clientToken) {
            $thread = Thread::whereKey($thread->id)->lockForUpdate()->firstOrFail();

            $existing = $thread->entries()->where('client_token', $clientToken)->first();
            if (!empty($existing)) {
                return $existing;
            }

            $entry = $thread->entries()->create([
                'kind' => ThreadEntry::KIND_EVENT,
                'author_side' => ThreadEntry::AUTHOR_SIDE_SYSTEM,
                'event_type' => $eventType,
                'event_payload' => $payload,
                'client_token' => $clientToken,
            ]);

            $thread->update([
                'last_entry_at' => $entry->created_at,
                'lock_version' => $thread->lock_version + 1,
            ]);

            return $entry;
        });
    }

    /**
     * @param ThreadEntry $entry
     * @param UploadedFile[] $files
     * @return void
     */
    private function storeAttachments(ThreadEntry $entry, array $files): void
    {
        foreach ($files as $file) {
            if (empty($file)) {
                continue;
            }
            $path = $file->store($this->fileStorageLayout->directoryFor(FileStorageLayout::AREA_THREAD_ATTACHMENTS));
            $entry->attachments()->create([
                'path' => $path,
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getClientMimeType(),
            ]);
        }
    }
}
