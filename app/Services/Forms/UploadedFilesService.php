<?php

declare(strict_types=1);

namespace App\Services\Forms;

use App\Eloquents\Answer;
use Illuminate\Support\Facades\Storage;

class UploadedFilesService
{
    public function getPathForAnswer(int $form_id, Answer $answer, int $question_id): ?string
    {
        if ($answer->form_id !== $form_id) {
            return null;
        }

        $path = $answer->details()
            ->where('question_id', $question_id)
            ->whereHas('question', function ($query) use ($form_id) {
                $query->where('form_id', $form_id)->where('type', 'upload');
            })
            ->value('answer');

        return $this->getPath($path);
    }

    public function getPath(?string $path): ?string
    {
        if (
            $path === null || !str_starts_with($path, 'answer_details/') ||
            str_contains($path, '\\') || str_contains($path, "\0")
        ) {
            return null;
        }

        // アップロード時に生成されるパスは answer_details 直下のファイルのみ。
        $filename = substr($path, strlen('answer_details/'));
        if ($filename === '' || $filename !== basename($filename)) {
            return null;
        }

        $directory = realpath(Storage::path('answer_details'));
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
