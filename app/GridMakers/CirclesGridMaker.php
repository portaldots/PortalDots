<?php

declare(strict_types=1);

namespace App\GridMakers;

use App\Eloquents\Circle;
use App\Eloquents\ParticipationType;
use App\Eloquents\Place;
use App\Eloquents\Tag;
use Illuminate\Database\Eloquent\Builder;
use App\GridMakers\Concerns\UseEloquent;
use App\GridMakers\Filter\FilterableKey;
use App\GridMakers\Filter\FilterableKeysDict;
use App\GridMakers\Helpers\AnswerDetailsHelper;
use Illuminate\Database\Eloquent\Model;
use App\Services\Utils\FormatTextService;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class CirclesGridMaker implements GridMakable
{
    use UseEloquent;

    private FormatTextService $formatTextService;

    private ?ParticipationType $participationType = null;

    public const PARTICIPATION_FORM_QUESTIONS_KEY_PREFIX = 'participation_form_question_';
    public const CHECKBOX_GROUP_CONCAT_SEPARATOR = "\n";

    public function __construct(FormatTextService $formatTextService)
    {
        $this->formatTextService = $formatTextService;
    }

    /**
     * 企画一覧を表示する参加種別をセット
     */
    public function withParticipationType(ParticipationType $participationType): self
    {
        $this->participationType = $participationType;
        $this->participationType->loadMissing(['form', 'form.questions']);
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function baseEloquentQuery(): Builder
    {
        $participationFormSelectFields =
            isset($this->participationType) ? AnswerDetailsHelper::getFormQuestionsKeys(
                $this->participationType->form->questions,
                self::PARTICIPATION_FORM_QUESTIONS_KEY_PREFIX
            ) : [];

        /** @var Builder */
        $query = Circle::submitted()->select([
            DB::raw('`circles`.`id` AS id'),
            DB::raw('`circles`.`participation_type_id` AS participation_type_id'),
            DB::raw('`circles`.`name` AS name'),
            DB::raw('`circles`.`name_yomi` AS name_yomi'),
            DB::raw('`circles`.`group_name` AS group_name'),
            DB::raw('`circles`.`group_name_yomi` AS group_name_yomi'),
            DB::raw('`circles`.`submitted_at` AS submitted_at'),
            DB::raw('`circles`.`status` AS status'),
            DB::raw('`circles`.`status_set_at` AS status_set_at'),
            DB::raw('`circles`.`status_set_by` AS status_set_by'),
            DB::raw('`circles`.`notes` AS notes'),
            DB::raw('`circles`.`created_at` AS created_at'),
            DB::raw('`circles`.`updated_at` AS updated_at'),
            ...$participationFormSelectFields,
        ])->with(['places', 'tags', 'statusSetBy', 'participationType']);

        if (isset($this->participationType)) {
            $query = $query->where('participation_type_id', $this->participationType->id);

            $query = $query->leftJoin('answers', function (JoinClause $join) {
                $join->on('circles.id', '=', 'answers.circle_id')
                    ->where('answers.form_id', $this->participationType->form_id);
            });

            $query = AnswerDetailsHelper::makeQueryWithAnswerDetails(
                $query,
                $this->participationType->form->questions,
                self::PARTICIPATION_FORM_QUESTIONS_KEY_PREFIX,
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
        $participationFormKeys = isset($this->participationType) ? AnswerDetailsHelper::getFormQuestionsKeys(
            $this->participationType->form->questions,
            self::PARTICIPATION_FORM_QUESTIONS_KEY_PREFIX
        ) : [];

        return [
            'id',
            'participation_type_id',
            'name',
            'name_yomi',
            'group_name',
            'group_name_yomi',
            'places',
            'tags',
            ...$participationFormKeys,
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

        $questionFilterableKeys = isset($this->participationType)
            ? AnswerDetailsHelper::makeFilterableKeysForAnswerDetails(
                $this->participationType->form->questions,
                self::PARTICIPATION_FORM_QUESTIONS_KEY_PREFIX
            ) : [];

        $participation_types_type = FilterableKey::belongsTo('participation_types', new FilterableKeysDict([
            'id' => FilterableKey::number(),
            'name' => FilterableKey::string(),
            'users_count_min' => FilterableKey::number(),
            'users_count_max' => FilterableKey::number(),
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
                    'participation_type_id' => $participation_types_type,
                    'name' => FilterableKey::string(),
                    'name_yomi' => FilterableKey::string(),
                    'group_name' => FilterableKey::string(),
                    'group_name_yomi' => FilterableKey::string(),
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
        $formKeys = isset($this->participationType) ? AnswerDetailsHelper::getFormQuestionsKeys(
            $this->participationType->form->questions,
            self::PARTICIPATION_FORM_QUESTIONS_KEY_PREFIX
        ) : [];

        return [
            'id',
            'participation_type_id',
            'name',
            'name_yomi',
            'group_name',
            'group_name_yomi',
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
        $itemsOfAnswerDetails = isset($this->participationType) ? AnswerDetailsHelper::mapForAnswerDetails(
            $record,
            $this->participationType->form->questions,
            $this->participationType->form,
            self::PARTICIPATION_FORM_QUESTIONS_KEY_PREFIX,
            self::CHECKBOX_GROUP_CONCAT_SEPARATOR
        ) : [];

        // カスタムフォームへの回答以外の項目
        $itemsExpectForms = [];

        $keysExceptParticipationForms = array_filter($this->keys(), function ($key) {
            return strpos($key, self::PARTICIPATION_FORM_QUESTIONS_KEY_PREFIX) !== 0;
        });

        foreach ($keysExceptParticipationForms as $key) {
            switch ($key) {
                case 'participation_type_id':
                    $itemsExpectForms[$key] = $record->participationType;
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
