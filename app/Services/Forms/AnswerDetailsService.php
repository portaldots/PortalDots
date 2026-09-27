<?php

declare(strict_types=1);

namespace App\Services\Forms;

use App\Eloquents\Form;
use App\Eloquents\Answer;
use App\Eloquents\AnswerRevision;
use App\Eloquents\Question;
use App\Eloquents\AnswerDetail;
use App\Http\Requests\Forms\AnswerRequestInterface;
use App\Services\Utils\ActivityLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class AnswerDetailsService
{
    /**
     * @var ActivityLogService
     */
    private $activityLogService;

    private array $newlyStoredFiles = [];

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * $answer に紐づく、設問に対する回答を取得
     *
     * @param Answer $answer
     * @return array
     */
    public function getAnswerDetailsByAnswer(Answer $answer)
    {
        $raw_details = AnswerDetail::where('answer_id', $answer->id)->get();
        $answer->loadMissing('form', 'form.questions');
        return $this->buildAnswerDetailsArray($answer->form->questions, $raw_details);
    }

    /**
     * $revision に記録された、提出当時の回答内容(スナップショット)から、
     * 設問に対する回答を取得
     *
     * @param Form $form
     * @param AnswerRevision $revision
     * @return array
     */
    public function getAnswerDetailsByRevision(Form $form, AnswerRevision $revision): array
    {
        $form->loadMissing('questions');
        return $this->buildAnswerDetailsArray($form->questions, $revision->details ?? []);
    }

    /**
     * 生の回答行(question_id, answer の組)の集まりから、画面表示用の
     * 配列(question_id をキーとする連想配列)を組み立てる
     *
     * @param iterable $questions
     * @param iterable $raw_rows 各要素は AnswerDetail モデル、または
     *  question_id・answer をキーに持つ配列(リビジョンのスナップショット)
     * @return array
     */
    private function buildAnswerDetailsArray(iterable $questions, iterable $raw_rows): array
    {
        $questions = collect($questions);
        $result = [];

        // チェックボックスの設問については、回答が配列になるようにする
        foreach ($raw_rows as $raw_row) {
            $question_id = (int)(is_array($raw_row) ? $raw_row['question_id'] : $raw_row->question_id);
            $answer_value = is_array($raw_row) ? $raw_row['answer'] : $raw_row->answer;
            $question = $questions->firstWhere('id', $question_id);

            if ($question instanceof Question && $question->type === 'checkbox') {
                if (empty($result[$question_id]) || !is_array($result[$question_id])) {
                    $result[$question_id] = [];
                }
                $result[$question_id][] = $answer_value;
            } elseif ($question instanceof Question && $question->type === 'table') {
                $result[$question_id] = self::decodeTableAnswerEnvelope($answer_value)['rows'];
            } else {
                $result[$question_id] = $answer_value;
            }
        }

        return $result;
    }

    /**
     * $answer の現在の回答内容(answer_details テーブルの行)を、
     * answer_revisions.details へ保存するためのスナップショットとして取得
     *
     * @param Answer $answer
     * @return array
     */
    public function snapshotAnswerDetailsForRevision(Answer $answer): array
    {
        return AnswerDetail::where('answer_id', $answer->id)
            ->get(['question_id', 'answer'])
            ->map(fn ($detail) => [
                'question_id' => $detail->question_id,
                'answer' => $detail->answer,
            ])
            ->values()
            ->all();
    }

    /**
     * Request オブジェクトより、必要に応じてファイルアップロードを行い、
     * ファイルアップロードの設問については、アップロード済ファイルのパスに
     * 置き換えた $answer_details 配列を return する
     *
     * @param Form $form
     * @param AnswerRequestInterface|null $request
     * @return array
     */
    public function getAnswerDetailsWithFilePathFromRequest(Form $form, ?AnswerRequestInterface $request = null)
    {
        if (empty($request)) {
            return [];
        }

        $form->loadMissing('questions');
        $answer_details = $request->validated()['answers'] ?? [];
        try {
            foreach ($form->questions as $question) {
                $file = $request->file('answers.' . $question->id);
                if ($question->type === 'upload' && isset($file)) {
                    $answer_details[$question->id] = $this->storeUploadedFile($file);
                    continue;
                }
                if ($question->type !== 'table' || !isset($answer_details[$question->id])) {
                    continue;
                }
                foreach ($question->table ?? [] as $column) {
                    if (($column['type'] ?? null) !== 'upload' || !isset($column['id'])) {
                        continue;
                    }
                    foreach ($answer_details[$question->id] as $rowId => $row) {
                        $cellFile = $request->file(
                            'answers.' . $question->id . '.' . $rowId . '.' . $column['id']
                        );
                        if (isset($cellFile)) {
                            $answer_details[$question->id][$rowId][$column['id']] =
                                $this->storeUploadedFile($cellFile);
                        }
                    }
                }
            }
        } catch (Throwable $e) {
            $this->discardNewlyStoredFiles();
            throw $e;
        }

        return $answer_details;
    }

    /**
     * @param Form $form
     * @param Answer $answer
     * @param array $answer_details
     * @param bool $keepAllFileVersions requires_review なフォームの回答では、
     *  過去のリビジョンから参照され続けるファイルを削除しないようにするため true にする
     */
    public function updateAnswerDetails(
        Form $form,
        Answer $answer,
        array $answer_details,
        bool $keepAllFileVersions = false
    ) {
        $answer_details_on_db = $this->getAnswerDetailsByAnswer($answer);
        $stored_details_on_db = AnswerDetail::where('answer_id', $answer->id)->get()->keyBy('question_id');

        AnswerDetail::where('answer_id', $answer->id)->delete();
        $form->loadMissing('questions');

        $data = [];
        foreach ($form->questions as $question) {
            if (isset($answer_details[$question->id])) {
                if ($question->type === 'table') {
                    $oldEnvelope = isset($stored_details_on_db[$question->id])
                        ? self::decodeTableAnswerEnvelope($stored_details_on_db[$question->id]->answer)
                        : ['rows' => [], 'columns' => []];
                    $envelope = $this->buildTableAnswerEnvelope(
                        $question,
                        $answer_details[$question->id],
                        $oldEnvelope
                    );
                    $data[] = [
                        'answer_id' => $answer->id,
                        'question_id' => $question->id,
                        'answer' => json_encode($envelope, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    ];
                } elseif (is_array($answer_details[$question->id])) {
                    foreach ($answer_details[$question->id] as $value) {
                        $data[] = [
                            'answer_id' => $answer->id,
                            'question_id' => $question->id,
                            'answer' => $value
                        ];
                    }
                } elseif ($question->type === 'upload' && $answer_details[$question->id] === '__KEEP__') {
                    // __KEEP__ の場合、アップロードされた値ではなく、現在の DB 上の値を
                    // そのまま回答として保存する
                    $data[] = [
                        'answer_id' => $answer->id,
                        'question_id' => $question->id,
                        'answer' => $answer_details_on_db[$question->id]
                    ];
                } elseif ($question->type === 'upload') {
                    $data[] = [
                        'answer_id' => $answer->id,
                        'question_id' => $question->id,
                        'answer' => $answer_details[$question->id]
                    ];
                } else {
                    $data[] = [
                        'answer_id' => $answer->id,
                        'question_id' => $question->id,
                        'answer' => $answer_details[$question->id]
                    ];
                }
            } else {
                // 回答がない設問は保存しない。旧ファイルは commit 後にまとめて削除する。
            }
        }

        AnswerDetail::insert($data);
        $answer->touch();

        // ログに残す
        $this->activityLogService->logOnlyAttributesChanged(
            'answer_detail',
            Auth::user(),
            $answer,
            $answer_details_on_db,
            $this->getAnswerDetailsByAnswer($answer)
        );

        if ($keepAllFileVersions) {
            // 差し替えられたファイルも過去のリビジョンから参照され続けるため、削除しない
            return;
        }

        $oldFilePaths = $this->collectStoredFilePaths($form, $stored_details_on_db->all());
        $newStoredDetails = AnswerDetail::where('answer_id', $answer->id)->get()->keyBy('question_id');
        $newFilePaths = $this->collectStoredFilePaths($form, $newStoredDetails->all());
        $filesToDelete = array_values(array_diff($oldFilePaths, $newFilePaths));
        if ($filesToDelete !== []) {
            DB::afterCommit(function () use ($filesToDelete) {
                try {
                    Storage::delete($filesToDelete);
                } catch (Throwable $e) {
                    report($e);
                }
            });
        }
    }

    public static function decodeTableAnswerEnvelope(?string $answer): array
    {
        if ($answer === null || $answer === '') {
            return ['rows' => [], 'columns' => []];
        }
        $decoded = json_decode($answer, true);
        if (!is_array($decoded)) {
            return ['rows' => [], 'columns' => []];
        }

        if (isset($decoded['rows']) && is_array($decoded['rows'])) {
            return [
                'rows' => $decoded['rows'],
                'columns' => isset($decoded['columns']) && is_array($decoded['columns'])
                    ? array_values($decoded['columns'])
                    : [],
            ];
        }

        // 初期実装などで行 map のみ保存された値も読み取れるようにする。
        return ['rows' => $decoded, 'columns' => []];
    }

    public function getTableAnswerEnvelopeByAnswer(Answer $answer, int $questionId): array
    {
        $stored = AnswerDetail::where('answer_id', $answer->id)
            ->where('question_id', $questionId)
            ->value('answer');
        return self::decodeTableAnswerEnvelope($stored);
    }

    public function getTableAnswerEnvelopeByRevision(AnswerRevision $revision, int $questionId): array
    {
        $row = collect($revision->details ?? [])->first(function ($row) use ($questionId) {
            return (int)($row['question_id'] ?? null) === $questionId;
        });
        return self::decodeTableAnswerEnvelope($row['answer'] ?? null);
    }

    public function discardNewlyStoredFiles(): void
    {
        if ($this->newlyStoredFiles !== []) {
            Storage::delete($this->newlyStoredFiles);
            $this->newlyStoredFiles = [];
        }
    }

    public function forgetNewlyStoredFiles(): void
    {
        $this->newlyStoredFiles = [];
    }

    private function storeUploadedFile($file): string
    {
        $path = $file->store('answer_details');
        $this->newlyStoredFiles[] = $path;
        return $path;
    }

    private function buildTableAnswerEnvelope(Question $question, array $submittedRows, array $oldEnvelope): array
    {
        $currentColumns = is_array($question->table) ? array_values($question->table) : [];
        $currentColumnIds = [];
        foreach ($currentColumns as $column) {
            if (isset($column['id'])) {
                $currentColumnIds[$column['id']] = $column['type'];
            }
        }

        $snapshot = $currentColumns;
        $deletedColumnIds = [];
        foreach ($oldEnvelope['columns'] as $column) {
            if (!isset($column['id']) || isset($currentColumnIds[$column['id']])) {
                continue;
            }
            $snapshot[] = $column;
            $deletedColumnIds[] = $column['id'];
        }

        $rows = [];
        foreach ($submittedRows as $rowId => $submittedRow) {
            if (!is_array($submittedRow)) {
                continue;
            }
            $row = [];
            foreach ($submittedRow as $columnId => $value) {
                if (($currentColumnIds[$columnId] ?? null) === 'upload' && $value === '__KEEP__') {
                    $value = $oldEnvelope['rows'][$rowId][$columnId] ?? null;
                    if (!is_string($value) || $value === '') {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            "answers.{$question->id}.{$rowId}.{$columnId}" =>
                                '保持するファイルが変更されています。画面を再読み込みしてください。',
                        ]);
                    }
                }
                if ($value !== null) {
                    $row[$columnId] = $value;
                }
            }
            foreach ($deletedColumnIds as $columnId) {
                if (array_key_exists($columnId, $oldEnvelope['rows'][$rowId] ?? [])) {
                    $row[$columnId] = $oldEnvelope['rows'][$rowId][$columnId];
                }
            }
            $rows[$rowId] = $row;
        }

        return ['rows' => $rows, 'columns' => $snapshot];
    }

    private function collectStoredFilePaths(Form $form, array $storedDetails): array
    {
        $form->loadMissing('questions');
        $questions = $form->questions->keyBy('id');
        $paths = [];
        foreach ($storedDetails as $detail) {
            $question = $questions->get($detail->question_id);
            if (!$question instanceof Question) {
                continue;
            }
            if ($question->type === 'upload') {
                if (is_string($detail->answer) && $detail->answer !== '') {
                    $paths[] = $detail->answer;
                }
                continue;
            }
            if ($question->type !== 'table') {
                continue;
            }
            $envelope = self::decodeTableAnswerEnvelope($detail->answer);
            $uploadColumnIds = [];
            foreach ($envelope['columns'] as $column) {
                if (($column['type'] ?? null) === 'upload' && isset($column['id'])) {
                    $uploadColumnIds[$column['id']] = true;
                }
            }
            foreach ($envelope['rows'] as $row) {
                if (!is_array($row)) {
                    continue;
                }
                foreach ($uploadColumnIds as $columnId => $unused) {
                    if (isset($row[$columnId]) && is_string($row[$columnId])) {
                        $paths[] = $row[$columnId];
                    }
                }
            }
        }
        return array_values(array_unique($paths));
    }

    /**
     * 指定された設問IDに対する回答を削除する
     *
     * @param int $question_id 設問ID
     */
    public function deleteAnswerDetailsByQuestionId(int $question_id)
    {
        // 削除対象モデルに対するdeletingとdeletedモデルイベントは発行されない
        $query = AnswerDetail::where('question_id', $question_id);
        if ($query->count() > 0) {
            $query->delete();
        }
    }
}
