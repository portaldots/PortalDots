<?php

declare(strict_types=1);

namespace App\Services\Threads;

use App\Eloquents\ThreadEntryAttachment;
use Illuminate\Support\Facades\Storage;

class ThreadAttachmentsService
{
    /**
     * 添付ファイルの実ファイルパスを取得する。thread_attachments 配下以外を
     * 指すパスや、実際には存在しないファイルの場合は null を返す
     *
     * @param ThreadEntryAttachment $attachment
     * @return string|null
     */
    public function getPath(ThreadEntryAttachment $attachment): ?string
    {
        $path = $attachment->path;

        if (
            !is_string($path) || !str_starts_with($path, 'thread_attachments/') ||
            str_contains($path, '\\') || str_contains($path, "\0")
        ) {
            return null;
        }

        $filename = substr($path, strlen('thread_attachments/'));
        if ($filename === '' || $filename !== basename($filename)) {
            return null;
        }

        $directory = realpath(Storage::path('thread_attachments'));
        $fullpath = realpath(Storage::path($path));
        if (
            $directory === false || $fullpath === false ||
            !str_starts_with($fullpath, $directory . DIRECTORY_SEPARATOR) ||
            !is_file($fullpath)
        ) {
            return null;
        }

        return $fullpath;
    }
}
