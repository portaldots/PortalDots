<?php

declare(strict_types=1);

namespace App\Services\Forms;

use App\Eloquents\Form;
use App\Eloquents\AnswerDetail;

class AnswerDetailsService
{
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
