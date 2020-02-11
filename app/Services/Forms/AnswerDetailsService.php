<?php

declare(strict_types=1);

namespace App\Services\Forms;

use App\Eloquents\Form;
use App\Eloquents\Answer;
use App\Eloquents\AnswerDetail;

class AnswerDetailsService
{
    public function getAnswerDetailsByAnswer(Answer $answer)
    {
        $raw_details = AnswerDetail::where('answer_id', $answer->id)->get();
        $answer->loadMissing('form.questions');
        $result = [];

        // チェックボックスの設問については、回答が配列になるようにする
        foreach ($raw_details as $raw_detail) {
            $question = $answer->form->questions->firstWhere('id', $raw_detail->question_id);

            if ($question->type === 'checkbox') {
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

    public function updateAnswerDetails(Answer $answer, array $answer_details)
    {
        AnswerDetail::where('answer_id', $answer->id)->delete();

        $data = [];
        foreach ($answer_details as $question_id => $detail) {
            if (is_array($detail)) {
                foreach ($detail as $value) {
                    $data[] = [
                        'answer_id' => $answer->id,
                        'question_id' => $question_id,
                        'answer' => $value
                    ];
                }
            } else {
                $data[] = [
                    'answer_id' => $answer->id,
                    'question_id' => $question_id,
                    'answer' => $detail
                ];
            }
        }

        AnswerDetail::insert($data);
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
