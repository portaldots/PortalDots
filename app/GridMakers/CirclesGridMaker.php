<?php

declare(strict_types=1);

namespace App\GridMakers;

use App\Eloquents\Circle;
use App\Eloquents\CustomForm;
use App\Eloquents\Form;
use App\Eloquents\Place;
use App\Eloquents\Tag;
use Illuminate\Database\Eloquent\Builder;
use App\GridMakers\Concerns\UseEloquent;
use App\GridMakers\Filter\FilterableKey;
use App\GridMakers\Filter\FilterableKeysDict;
use App\GridMakers\Helpers\AnswerDetailsHelper;
use Illuminate\Database\Eloquent\Model;
use App\Services\Utils\FormatTextService;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class CirclesGridMaker implements GridMakable
{
    use UseEloquent;

    /**
     * @var FormatTextService
     */
    private $formatTextService;

    /**
     * @var Form
     */
    private $custom_form;

    public const CUSTOM_FORM_QUESTIONS_KEY_PREFIX = 'custom_form_question_';
    public const CHECKBOX_GROUP_CONCAT_SEPARATOR = "\n";

    public function __construct(FormatTextService $formatTextService)
    {
        $this->formatTextService = $formatTextService;
        $this->custom_form = CustomForm::getFormByType('circle');
    }

    /**
     * @inheritDoc
     */
    protected function baseEloquentQuery(): Builder
    {
        $customFormSelectFields =
            isset($this->custom_form) ? AnswerDetailsHelper::getFormQuestionsKeys(
                $this->custom_form->questions,
                self::CUSTOM_FORM_QUESTIONS_KEY_PREFIX
            ) : [];

        /** @var Builder */
        $query = Circle::submitted()->select([
            DB::raw('`circles`.`id` AS id'),
            DB::raw('`circles`.`name` AS name'),
            DB::raw('`circles`.`name_yomi` AS name_yomi'),
            DB::raw('`groups`.`is_individual` AS is_individual'),
            DB::raw('`circles`.`group_id` AS group_id'),
            DB::raw('`circles`.`submitted_at` AS submitted_at'),
            DB::raw('`circles`.`status` AS status'),
            DB::raw('`circles`.`status_set_at` AS status_set_at'),
            DB::raw('`circles`.`status_set_by` AS status_set_by'),
            DB::raw('`circles`.`notes` AS notes'),
            DB::raw('`circles`.`created_at` AS created_at'),
            DB::raw('`circles`.`updated_at` AS updated_at'),
            ...$customFormSelectFields,
        ])->with([
            'group',
            'group.users' => function (EloquentBuilder $query) {
                // 責任者の情報のみを取得する。
                $query->select([
                    'id', 'student_id', 'name_family', 'name_family_yomi', 'name_given', 'name_given_yomi'
                ])->wherePivot('role', 'owner');
            },
            'places',
            'tags',
            'statusSetBy'
        ])->leftJoin('groups', function (JoinClause $join) {
            $join->on('circles.group_id', '=', 'groups.id');
        });

        if (isset($this->custom_form)) {
            $query = $query->leftJoin('answers', function (JoinClause $join) {
                $join->on('circles.id', '=', 'answers.circle_id')
                    ->where('answers.form_id', $this->custom_form->id);
            });

            $query = AnswerDetailsHelper::makeQueryWithAnswerDetails(
                $query,
                $this->custom_form->questions,
                self::CUSTOM_FORM_QUESTIONS_KEY_PREFIX,
                self::CHECKBOX_GROUP_CONCAT_SEPARATOR
            );
        }

        return $query;
    }

    /**
     * @inheritDoc
     */
    public function keys(): array
    {
        $customFormKeys = isset($this->custom_form) ? AnswerDetailsHelper::getFormQuestionsKeys(
            $this->custom_form->questions,
            self::CUSTOM_FORM_QUESTIONS_KEY_PREFIX
        ) : [];

        return [
            'id',
            'name',
            'name_yomi',
            'is_individual',
            'group_id',
            'users',
            'places',
            'tags',
            ...$customFormKeys,
            'submitted_at',
            'status',
            'status_set_at',
            'status_set_by',
            'notes',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * @inheritDoc
     */
    public function filterableKeys(): FilterableKeysDict
    {
        static $tags_choices = null;
        static $places_choices = null;

        if (empty($tags_choices)) {
            $tags_choices = Tag::all()->toArray();
        }

        if (empty($places_choices)) {
            $places_choices = Place::all()->toArray();
        }

        $questionFilterableKeys = isset($this->custom_form)
            ? AnswerDetailsHelper::makeFilterableKeysForAnswerDetails(
                $this->custom_form->questions,
                self::CUSTOM_FORM_QUESTIONS_KEY_PREFIX
            ) : [];

        $group_type = FilterableKey::belongsTo('groups', new FilterableKeysDict([
            'id' => FilterableKey::number(),
            'name' => FilterableKey::string(),
            'name_yomi' => FilterableKey::string(),
            'notes' => FilterableKey::string(),
            'created_at' => FilterableKey::datetime(),
            'updated_at' => FilterableKey::datetime(),
        ]));

        $users_type = FilterableKey::belongsTo('users', new FilterableKeysDict([
            'id' => FilterableKey::number(),
            'student_id' => FilterableKey::string(),
            'name_family' => FilterableKey::string(),
            'name_family_yomi' => FilterableKey::string(),
            'name_given' => FilterableKey::string(),
            'name_given_yomi' => FilterableKey::string(),
            'email' => FilterableKey::string(),
            'tel' => FilterableKey::string(),
            'is_staff' => FilterableKey::bool(),
            'is_admin' => FilterableKey::bool(),
            'email_verified_at' => FilterableKey::isNull(),
            'univemail_verified_at' => FilterableKey::isNull(),
            'notes' => FilterableKey::string(),
            'created_at' => FilterableKey::datetime(),
            'updated_at' => FilterableKey::datetime(),
        ]));

        // 連想配列をスプレッド演算子で結合できるのは PHP 8.1 以降。
        // PortalDots は PHP 8.0 以上をサポート対象とするため、スプレッド演算子を利用できない。
        return new FilterableKeysDict(
            array_merge(
                [
                    'id' => FilterableKey::number(),
                    'name' => FilterableKey::string(),
                    'name_yomi' => FilterableKey::string(),
                    'is_individual' => FilterableKey::enum([1, 0]),
                    'group_id' => $group_type,
                    'places' => FilterableKey::belongsToMany(
                        'booths',
                        'circle_id',
                        'place_id',
                        $places_choices,
                        'name'
                    ),
                    'tags' => FilterableKey::belongsToMany(
                        'circle_tag',
                        'circle_id',
                        'tag_id',
                        $tags_choices,
                        'name'
                    ),
                ],
                $questionFilterableKeys,
                [
                    'submitted_at' => FilterableKey::datetime(),
                    // 不受理、受理、確認中
                    'status' => FilterableKey::enum(['rejected', 'approved', 'NULL']),
                    'status_set_at' => FilterableKey::datetime(),
                    'status_set_by' => $users_type,
                    'notes' => FilterableKey::string(),
                    'created_at' => FilterableKey::datetime(),
                    'updated_at' => FilterableKey::datetime(),
                ]
            )
        );
    }

    /**
     * @inheritDoc
     */
    public function sortableKeys(): array
    {
        $formKeys = isset($this->custom_form) ? AnswerDetailsHelper::getFormQuestionsKeys(
            $this->custom_form->questions,
            self::CUSTOM_FORM_QUESTIONS_KEY_PREFIX
        ) : [];

        return [
            'id',
            'name',
            'is_individual',
            'group_id',
            ...$formKeys,
            'submitted_at',
            'status',
            'status_set_at',
            'status_set_by',
            'notes',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * @inheritDoc
     */
    public function map($record): array
    {
        // カスタムフォームへの回答
        $itemsOfAnswerDetails = isset($this->custom_form) ? AnswerDetailsHelper::mapForAnswerDetails(
            $record,
            $this->custom_form->questions,
            $this->custom_form,
            self::CUSTOM_FORM_QUESTIONS_KEY_PREFIX,
            self::CHECKBOX_GROUP_CONCAT_SEPARATOR
        ) : [];

        // カスタムフォームへの回答以外の項目
        $itemsExpectForms = [];

        $keysExceptCustomForms = array_filter($this->keys(), function ($key) {
            return strpos($key, self::CUSTOM_FORM_QUESTIONS_KEY_PREFIX) !== 0;
        });

        foreach ($keysExceptCustomForms as $key) {
            switch ($key) {
                case 'is_individual':
                    $itemsExpectForms[$key] = (int)$record->is_individual === 1;
                    break;
                case 'group_id':
                    $itemsExpectForms[$key] = $record->group;
                    break;
                case 'users':
                    $itemsExpectForms[$key] = $record->group->users[0] ?? null;
                    break;
                case 'status_set_by':
                    $itemsExpectForms[$key] = $record->statusSetBy;
                    break;
                case 'status_set_at':
                    $itemsExpectForms[$key] = !empty($record->status_set_at)
                        ? $record->status_set_at->format('Y/m/d H:i:s') : null;
                    break;
                case 'created_at':
                    $itemsExpectForms[$key] = !empty($record->created_at)
                        ? $record->created_at->format('Y/m/d H:i:s') : null;
                    break;
                case 'updated_at':
                    $itemsExpectForms[$key] = !empty($record->updated_at)
                        ? $record->updated_at->format('Y/m/d H:i:s') : null;
                    break;
                default:
                    $itemsExpectForms[$key] = $record->$key;
            }
        }

        return array_merge($itemsExpectForms, $itemsOfAnswerDetails);
    }

    protected function model(): Model
    {
        return new Circle();
    }
}
