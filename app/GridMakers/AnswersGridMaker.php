<?php

declare(strict_types=1);

namespace App\GridMakers;

use App\Eloquents\Answer;
use App\Eloquents\AnswerDetail;
use App\Eloquents\Form;
use App\Eloquents\Question;
use Illuminate\Database\Eloquent\Builder;
use App\GridMakers\Concerns\UseEloquent;
use App\GridMakers\Filter\FilterableKey;
use App\GridMakers\Filter\FilterableKeysDict;
use Illuminate\Database\Eloquent\Model;
use App\Services\Utils\FormatTextService;
use Illuminate\Database\Query\JoinClause;

class AnswersGridMaker implements GridMakable
{
    use UseEloquent;

    /**
     * @var FormatTextService
     */
    private $formatTextService;

    /**
     * @var Form
     */
    private $form;

    public const FORM_QUESTIONS_KEY_PREFIX = 'form_question_';
    public const CHECKBOX_GROUP_CONCAT_SEPARATOR = "\n";

    public function __construct(FormatTextService $formatTextService)
    {
        $this->formatTextService = $formatTextService;
    }

    /**
     * 回答一覧を表示したいフォームをセット
     *
     * @param Form $form
     * @return $this
     */
    public function withForm(Form $form)
    {
        $this->form = $form;
        $this->form->loadMissing(['questions' => function ($query) {
            $query->where('type', '!=', 'heading');
        }]);
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function baseEloquentQuery(): Builder
    {
        $questionColumns = $this->form->questions
            ->map(function (Question $question) {
                $idInt = intval($question->id);
                if ($idInt === 0 || !is_int($idInt)) {
                    return null;
                }
                // SQLインジェクションに注意。$idInt は整数であることを期待している。
                $columnAlias = self::FORM_QUESTIONS_KEY_PREFIX . $idInt;

                switch ($question->type) {
                    case 'heading':
                        return null;
                    case 'number':
                        return "MAX(CAST(CASE WHEN question_id = {$idInt} THEN answer ELSE NULL END AS DECIMAL(10, 0))) AS '{$columnAlias}'";
                    case 'text':
                    case 'textarea':
                    case 'radio':
                    case 'select':
                    case 'upload':
                        return "MAX(CASE WHEN question_id = {$idInt} THEN answer ELSE NULL END) AS '{$columnAlias}'";
                    case 'checkbox':
                        $separator = self::CHECKBOX_GROUP_CONCAT_SEPARATOR;
                        return "GROUP_CONCAT(CASE WHEN question_id = {$idInt} THEN answer ELSE NULL END SEPARATOR '{$separator}') AS '{$columnAlias}'";
                }
            })
            ->filter(function ($column) {
                return !is_null($column);
            });

        $answerDetailsSubQueryColumns = ['answer_id', ...$questionColumns];

        $answerDetailsSubQuery = AnswerDetail::selectRaw(implode(', ', $answerDetailsSubQueryColumns))
            ->groupBy('answer_id');

        $query = Answer::with('circle')
            ->leftJoinSub($answerDetailsSubQuery, 'pivot_answer_details', function (JoinClause $join) {
                $join->on('answers.id', '=', 'pivot_answer_details.answer_id');
            })
            ->where('form_id', $this->form->id);

        return $query;
    }

    private function getFormQuestionKey(Question $question): string
    {
        return self::FORM_QUESTIONS_KEY_PREFIX . $question->id;
    }

    private function getFormQuestionsKeys(): array
    {
        return
            isset($this->form) ?
            $this->form->questions->map(function (Question $question) {
                return $this->getFormQuestionKey($question);
            })->all() : [];
    }

    /**
     * @inheritDoc
     */
    public function keys(): array
    {
        $form_keys = $this->getFormQuestionsKeys();

        return [
            'id',
            'circle_id',
            'created_at',
            'updated_at',
            ...$form_keys,
        ];
    }

    /**
     * @inheritDoc
     */
    public function filterableKeys(): FilterableKeysDict
    {
        $circles_type = FilterableKey::belongsTo('circles', new FilterableKeysDict([
            'id' => FilterableKey::number(),
            'name' => FilterableKey::string(),
            'name_yomi' => FilterableKey::string(),
            'group_name' => FilterableKey::string(),
            'group_name_yomi' => FilterableKey::string(),
            'submitted_at' => FilterableKey::datetime(),
            'status_set_at' => FilterableKey::datetime(),
            'notes' => FilterableKey::string(),
            'created_at' => FilterableKey::datetime(),
            'updated_at' => FilterableKey::datetime(),
        ]));

        $questionFilterableKeys = [];
        foreach ($this->form->questions as $question) {
            $questionKey = $this->getFormQuestionKey($question);
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

        // 連想配列をスプレッド演算子で結合できるのは PHP 8.1 以降。
        // PortalDots は PHP 8.0 以上をサポート対象とするため、スプレッド演算子を利用できない。
        return new FilterableKeysDict(array_merge(
            [
                'id' => FilterableKey::number(),
                'circle_id' => $circles_type,
                'created_at' => FilterableKey::datetime(),
                'updated_at' => FilterableKey::datetime(),
            ],
            $questionFilterableKeys
        ));
    }

    /**
     * @inheritDoc
     */
    public function sortableKeys(): array
    {
        $form_keys = $this->getFormQuestionsKeys();

        return [
            'id',
            'circle_id',
            'created_at',
            'updated_at',
            ...$form_keys,
        ];
    }

    /**
     * @inheritDoc
     */
    public function map($record): array
    {
        $item = [];

        // フォームへの回答
        foreach ($this->getFormQuestionsKeys() as $formKey) {
            $questionId = intval(str_replace(self::FORM_QUESTIONS_KEY_PREFIX, '', $formKey));
            $question = $this->form->questions->firstWhere('id', $questionId);
            $answerValue = $record->$formKey;

            if ($question->type === 'upload') {
                $item[$formKey] = !empty($answerValue) ? [
                    'file_url' => route('staff.forms.answers.uploads.show', [
                        'form' => $this->form->id,
                        'answer' => $record->id,
                        'question' => $questionId,
                    ])
                ] : [];
            } elseif ($question->type === 'checkbox') {
                $item[$formKey] = isset($answerValue) ? explode(self::CHECKBOX_GROUP_CONCAT_SEPARATOR, $answerValue) : [];
            } else {
                $item[$formKey] = [$answerValue];
            }
        }

        // カスタムフォームへの回答以外の項目
        $keys_except_forms = array_filter($this->keys(), function ($key) {
            return strpos($key, self::FORM_QUESTIONS_KEY_PREFIX) !== 0;
        });

        foreach ($keys_except_forms as $key) {
            switch ($key) {
                case 'circle_id':
                    $item[$key] = $record->circle->only(['id', 'name', 'name_yomi', 'group_name', 'group_name_yomi']);
                    break;
                case 'created_at':
                    $item[$key] = !empty($record->created_at) ? $record->created_at->format('Y/m/d H:i:s') : null;
                    break;
                case 'updated_at':
                    $item[$key] = !empty($record->updated_at) ? $record->updated_at->format('Y/m/d H:i:s') : null;
                    break;
                default:
                    $item[$key] = $record->$key;
            }
        }

        return $item;
    }

    protected function model(): Model
    {
        return new Answer();
    }
}
