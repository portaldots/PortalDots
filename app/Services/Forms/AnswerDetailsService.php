<?php

declare(strict_types=1);

namespace App\Services\Forms;

use App\Eloquents\Form;
use App\Eloquents\Answer;
use App\Eloquents\Question;
use App\Eloquents\AnswerDetail;
use App\Http\Requests\Forms\AnswerRequestInterface;
use App\Services\Utils\ActivityLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AnswerDetailsService
{
    /**
     * @var ActivityLogService
     */
    private $activityLogService;

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
        $result = [];

        // チェックボックスの設問については、回答が配列になるようにする
        foreach ($raw_details as $raw_detail) {
            $question = $answer->form->questions->firstWhere('id', $raw_detail->question_id);

            if ($question instanceof Question && $question->type === 'checkbox') {
                if (empty($result[$raw_detail->question_id]) || !is_array($result[$raw_detail->question_id])) {
                    $result[$raw_detail->question_id] = [];
                }
                $result[$raw_detail->question_id][] = $raw_detail->answer;
            } else {
                $result[$raw_detail->question_id] = $raw_detail->answer;
            }
        }

        return $result;
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
        foreach ($form->questions as $question) {
            $file = $request->file('answers.' . $question->id);
            if ($question->type === 'upload' && isset($file)) {
                $answer_details[$question->id] = $file->store('answer_details');
            }
        }

        return $answer_details;
    }

    public function updateAnswerDetails(Form $form, Answer $answer, array $answer_details)
    {
        $answer_details_on_db = $this->getAnswerDetailsByAnswer($answer);

        AnswerDetail::where('answer_id', $answer->id)->delete();
        $form->loadMissing('questions');

        $data = [];
        foreach ($form->questions as $question) {
            if (isset($answer_details[$question->id])) {
                if (is_array($answer_details[$question->id])) {
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
                    if (isset($answer_details_on_db[$question->id])) {
                        Storage::delete($answer_details_on_db[$question->id]);
                    }
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
                if ($question->type === 'upload' && isset($answer_details_on_db[$question->id])) {
                    Storage::delete($answer_details_on_db[$question->id]);
                }
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
