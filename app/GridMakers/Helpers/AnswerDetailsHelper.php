<?php

declare(strict_types=1);

namespace App\GridMakers\Helpers;

use App\Eloquents\AnswerDetail;
use App\Eloquents\Form;
use App\Eloquents\Question;
use App\GridMakers\Filter\FilterableKey;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\JoinClause;

class AnswerDetailsHelper
{
    /**
     * 与えられたクエリビルダーに、フォーム設問への回答(answer_details)の情報を取得する機能を追加する。
     *
     * @param Builder $query
     * @param Collection<Question> $questions
     * @param string $questionsKeyPrefix
     * @param string $checkboxGroupConcatSeparator
     * @return Builder
     */
    public static function makeQueryWithAnswerDetails(Builder $query, Collection $questions, string $questionsKeyPrefix, string $checkboxGroupConcatSeparator): Builder
    {
        $questionColumns = $questions
            ->map(function (Question $question) use ($questionsKeyPrefix, $checkboxGroupConcatSeparator) {
                $idInt = intval($question->id);
                if ($idInt === 0 || !is_int($idInt)) {
                    return null;
                }
                // SQLインジェクションに注意。$idInt は整数であることを期待している。
                $columnAlias = $questionsKeyPrefix . $idInt;

                switch ($question->type) {
                    case 'heading':
                        return null;
                    case 'number':
                        // phpcs:ignore
                        return "MAX(CAST(CASE WHEN question_id = {$idInt} THEN answer ELSE NULL END AS DECIMAL(10, 0))) AS '{$columnAlias}'";
                    case 'text':
                    case 'textarea':
                    case 'radio':
                    case 'select':
                    case 'upload':
                        return "MAX(CASE WHEN question_id = {$idInt} THEN answer ELSE NULL END) AS '{$columnAlias}'";
                    case 'checkbox':
                        $separator = $checkboxGroupConcatSeparator;
                        // phpcs:ignore
                        return "GROUP_CONCAT(CASE WHEN question_id = {$idInt} THEN answer ELSE NULL END SEPARATOR '{$separator}') AS '{$columnAlias}'";
                }
            })
            ->filter(function ($column) {
                return !is_null($column);
            });

        $answerDetailsSubQueryColumns = ['answer_id', ...$questionColumns];

        $answerDetailsSubQuery = AnswerDetail::selectRaw(implode(', ', $answerDetailsSubQueryColumns))
            ->groupBy('answer_id');

        return $query->leftJoinSub($answerDetailsSubQuery, 'pivot_answer_details', function (JoinClause $join) {
            $join->on('answers.id', '=', 'pivot_answer_details.answer_id');
        });
    }

    public static function getFormQuestionKey(Question $question, string $questionsKeyPrefix): string
    {
        return $questionsKeyPrefix . $question->id;
    }

    public static function getFormQuestionsKeys(Collection $questions, string $questionsKeyPrefix): array
    {
        return
            $questions->map(function (Question $question) use ($questionsKeyPrefix) {
                if ($question->type === 'heading') {
                    return null;
                }
                return self::getFormQuestionKey($question, $questionsKeyPrefix);
            })
            ->filter(function ($column) {
                return !is_null($column);
            })
            ->all();
    }

    public static function makeFilterableKeysForAnswerDetails(Collection $questions, string $questionsKeyPrefix): array
    {
        $questionFilterableKeys = [];
        foreach ($questions as $question) {
            $questionKey = self::getFormQuestionKey($question, $questionsKeyPrefix);
            switch ($question->type) {
                case 'heading':
                case 'upload':
                    break;
                case 'number':
                    $questionFilterableKeys[$questionKey] = FilterableKey::number();
                    break;
                case 'text':
                case 'textarea':
                case 'radio':
                case 'checkbox':
                case 'select':
                    $questionFilterableKeys[$questionKey] = FilterableKey::string();
                    break;
                default:
                    break;
            }
        }
        return $questionFilterableKeys;
    }

    public static function mapForAnswerDetails(
        $record,
        Collection $questions,
        Form $form,
        string $questionsKeyPrefix,
        string $checkboxGroupConcatSeparator
    ): array {
        $item = [];

        foreach (self::getFormQuestionsKeys($questions, $questionsKeyPrefix) as $formKey) {
            $questionId = intval(str_replace($questionsKeyPrefix, '', $formKey));
            $question = $questions->firstWhere('id', $questionId);
            $answerValue = $record->$formKey;

            if ($question->type === 'upload') {
                $item[$formKey] = !empty($answerValue) ? [
                    'file_url' => route('staff.forms.answers.uploads.show', [
                        'form' => $form->id,
                        'answer' => $record->id,
                        'question' => $questionId,
                    ])
                ] : [];
            } elseif ($question->type === 'checkbox') {
                $item[$formKey] = isset($answerValue)
                    ? explode($checkboxGroupConcatSeparator, $answerValue)
                    : [];
            } else {
                $item[$formKey] = [$answerValue];
            }
        }

        return $item;
    }
}
