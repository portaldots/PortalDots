<?php

declare(strict_types=1);

namespace App\GridMakers;

use App\Eloquents\Answer;
use App\Eloquents\Form;
use Illuminate\Database\Eloquent\Builder;
use App\GridMakers\Concerns\UseEloquent;
use App\GridMakers\Filter\FilterableKey;
use App\GridMakers\Filter\FilterableKeysDict;
use App\GridMakers\Helpers\AnswerDetailsHelper;
use Illuminate\Database\Eloquent\Model;
use App\Services\Utils\FormatTextService;

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
        $baseQuery = Answer::select($this->keys())
            ->with('circle')
            ->where('form_id', $this->form->id);

        $query = AnswerDetailsHelper::makeQueryWithAnswerDetails(
            $baseQuery,
            $this->form->questions,
            self::FORM_QUESTIONS_KEY_PREFIX,
            self::CHECKBOX_GROUP_CONCAT_SEPARATOR
        );

        return $query;
    }

    /**
     * @inheritDoc
     */
    public function keys(): array
    {
        $form_keys = AnswerDetailsHelper::getFormQuestionsKeys(
            $this->form->questions,
            self::FORM_QUESTIONS_KEY_PREFIX
        );

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

        $questionFilterableKeys = AnswerDetailsHelper::makeFilterableKeysForAnswerDetails(
            $this->form->questions,
            self::FORM_QUESTIONS_KEY_PREFIX
        );

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
        $formKeys = AnswerDetailsHelper::getFormQuestionsKeys(
            $this->form->questions,
            self::FORM_QUESTIONS_KEY_PREFIX
        );

        return [
            'id',
            'circle_id',
            'created_at',
            'updated_at',
            ...$formKeys,
        ];
    }

    /**
     * @inheritDoc
     */
    public function map($record): array
    {
        // フォームへの回答
        $itemsOfAnswerDetails = AnswerDetailsHelper::mapForAnswerDetails(
            $record,
            $this->form->questions,
            $this->form,
            self::FORM_QUESTIONS_KEY_PREFIX,
            self::CHECKBOX_GROUP_CONCAT_SEPARATOR
        );

        // カスタムフォームへの回答以外の項目
        $itemsExceptForms = [];

        $keysExceptForms = array_filter($this->keys(), function ($key) {
            return strpos($key, self::FORM_QUESTIONS_KEY_PREFIX) !== 0;
        });

        foreach ($keysExceptForms as $key) {
            switch ($key) {
                case 'circle_id':
                    $itemsExceptForms[$key] = $record->circle->only(['id', 'name', 'name_yomi', 'group_name', 'group_name_yomi']);
                    break;
                case 'created_at':
                    $itemsExceptForms[$key] = !empty($record->created_at) ? $record->created_at->format('Y/m/d H:i:s') : null;
                    break;
                case 'updated_at':
                    $itemsExceptForms[$key] = !empty($record->updated_at) ? $record->updated_at->format('Y/m/d H:i:s') : null;
                    break;
                default:
                    $itemsExceptForms[$key] = $record->$key;
            }
        }

        return array_merge($itemsExceptForms, $itemsOfAnswerDetails);
    }

    protected function model(): Model
    {
        return new Answer();
    }
}
