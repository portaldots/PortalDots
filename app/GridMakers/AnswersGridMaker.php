<?php

declare(strict_types=1);

namespace App\GridMakers;

use App\Eloquents\Answer;
use App\Eloquents\Form;
use App\Eloquents\Place;
use App\Eloquents\Question;
use App\Eloquents\Tag;
use Illuminate\Database\Eloquent\Builder;
use App\GridMakers\Concerns\UseEloquent;
use App\GridMakers\Filter\FilterableKey;
use App\GridMakers\Filter\FilterableKeysDict;
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
        return Answer::select([
            'id',
            'circle_id',
            'created_at',
            'updated_at',
        ])->with('details', 'details.question', 'circle')->where('form_id', $this->form->id);
    }

    /**
     * @inheritDoc
     */
    public function keys(): array
    {
        // 現状 PortalDots は PHP7.3 以降をサポートすることにしているため、
        // PHP 7.4 からサポートされるスプレッド演算子を使わず、array_merge を使っている

        $before_form_keys = [
            'id',
            'circle_id',
            'created_at',
            'updated_at',
        ];

        $form_keys = isset($this->form) ?
            $this->form->questions->map(function (Question $question) {
                return self::FORM_QUESTIONS_KEY_PREFIX . $question->id;
            })->all() : [];

        return array_merge($before_form_keys, $form_keys);
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

        $circles_type = FilterableKey::belongsTo('circles', new FilterableKeysDict([
            'id' => FilterableKey::number(),
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
            'submitted_at' => FilterableKey::datetime(),
            'status_set_at' => FilterableKey::datetime(),
            'notes' => FilterableKey::string(),
            'created_at' => FilterableKey::datetime(),
            'updated_at' => FilterableKey::datetime(),
        ]));

        return new FilterableKeysDict([
            'id' => FilterableKey::number(),
            'circle_id' => $circles_type,
            'created_at' => FilterableKey::datetime(),
            'updated_at' => FilterableKey::datetime(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function sortableKeys(): array
    {
        return [
            'id',
            'circle_id',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * @inheritDoc
     */
    public function map($record): array
    {
        $item = [];

        // フォームへの回答
        if (isset($record->details) && is_iterable($record->details)) {
            foreach ($record->details as $detail) {
                if ($detail->question->type === 'upload') {
                    $item[self::FORM_QUESTIONS_KEY_PREFIX . $detail->question_id] = [
                        'file_url' => route('staff.forms.answers.uploads.show', [
                            'form' => $this->form->id,
                            'answer' => $record->id,
                            'question' => $detail->question_id
                        ])
                    ];
                } elseif (
                    isset($item[self::FORM_QUESTIONS_KEY_PREFIX . $detail->question_id]) &&
                    is_array($item[self::FORM_QUESTIONS_KEY_PREFIX . $detail->question_id])
                ) {
                    $item[self::FORM_QUESTIONS_KEY_PREFIX . $detail->question_id][] = $detail->answer;
                } else {
                    $item[self::FORM_QUESTIONS_KEY_PREFIX . $detail->question_id] = [$detail->answer];
                }
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
