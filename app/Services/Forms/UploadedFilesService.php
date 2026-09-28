<?php

declare(strict_types=1);

namespace App\Services\Forms;

use App\Contracts\FileStorageLayout;
use App\Eloquents\Answer;
use App\Eloquents\AnswerRevision;
use App\Eloquents\Question;
use Illuminate\Support\Facades\Storage;

class UploadedFilesService
{
    private FileStorageLayout $fileStorageLayout;

    public function __construct(FileStorageLayout $fileStorageLayout)
    {
        $this->fileStorageLayout = $fileStorageLayout;
    }

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

    public function getPathForTableAnswer(
        int $form_id,
        Answer $answer,
        int $question_id,
        string $row_id,
        string $column_id
    ): ?string {
        if ($answer->form_id !== $form_id) {
            return null;
        }

        $detail = $answer->details()
            ->where('question_id', $question_id)
            ->whereHas('question', function ($query) use ($form_id) {
                $query->where('form_id', $form_id)->where('type', 'table');
            })
            ->first();
        if ($detail === null) {
            return null;
        }

        $envelope = AnswerDetailsService::decodeTableAnswerEnvelope($detail->answer);
        $column = collect($envelope['columns'])->firstWhere('id', $column_id);
        if (!is_array($column) || ($column['type'] ?? null) !== 'upload') {
            return null;
        }

        $path = $envelope['rows'][$row_id][$column_id] ?? null;
        return is_string($path) ? $this->getPath($path) : null;
    }

    /**
     * $revision に記録されたスナップショットから、アップロード設問への
     * 回答ファイルのパスを取得する。そのリビジョンに記録されていないパスは返さない
     */
    public function getPathForRevisionAnswer(
        int $form_id,
        Answer $answer,
        AnswerRevision $revision,
        int $question_id
    ): ?string {
        if ($answer->form_id !== $form_id || (int)$revision->answer_id !== $answer->id) {
            return null;
        }

        $isUploadQuestion = Question::where('id', $question_id)
            ->where('form_id', $form_id)
            ->where('type', 'upload')
            ->exists();
        if (!$isUploadQuestion) {
            return null;
        }

        $row = collect($revision->details ?? [])->first(function ($row) use ($question_id) {
            return (int)($row['question_id'] ?? null) === $question_id;
        });

        $path = $row['answer'] ?? null;
        return is_string($path) ? $this->getPath($path) : null;
    }

    /**
     * $revision に記録されたスナップショットから、表形式の設問内の
     * アップロード列に対する回答ファイルのパスを取得する。
     * そのリビジョンに記録されていないパスは返さない
     */
    public function getPathForRevisionTableAnswer(
        int $form_id,
        Answer $answer,
        AnswerRevision $revision,
        int $question_id,
        string $row_id,
        string $column_id
    ): ?string {
        if ($answer->form_id !== $form_id || (int)$revision->answer_id !== $answer->id) {
            return null;
        }

        $isTableQuestion = Question::where('id', $question_id)
            ->where('form_id', $form_id)
            ->where('type', 'table')
            ->exists();
        if (!$isTableQuestion) {
            return null;
        }

        $detailRow = collect($revision->details ?? [])->first(function ($row) use ($question_id) {
            return (int)($row['question_id'] ?? null) === $question_id;
        });
        if ($detailRow === null) {
            return null;
        }

        $envelope = AnswerDetailsService::decodeTableAnswerEnvelope($detailRow['answer'] ?? null);
        $column = collect($envelope['columns'])->firstWhere('id', $column_id);
        if (!is_array($column) || ($column['type'] ?? null) !== 'upload') {
            return null;
        }

        $path = $envelope['rows'][$row_id][$column_id] ?? null;
        return is_string($path) ? $this->getPath($path) : null;
    }

    public function getPath(?string $path): ?string
    {
        $area = $this->fileStorageLayout->directoryFor(FileStorageLayout::AREA_ANSWER_DETAILS);
        $prefix = $area . '/';

        if (
            $path === null || !str_starts_with($path, $prefix) ||
            str_contains($path, '\\') || str_contains($path, "\0")
        ) {
            return null;
        }

        // アップロード時に生成されるパスは保存先ディレクトリ直下のファイルのみ。
        $filename = substr($path, strlen($prefix));
        if ($filename === '' || $filename !== basename($filename)) {
            return null;
        }

        $directory = realpath(Storage::path($area));
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
