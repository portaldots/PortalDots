<?php

declare(strict_types=1);

namespace App\GridMakers;

use App\Eloquents\Tag;
use Illuminate\Database\Eloquent\Builder;
use App\Eloquents\Form;
use App\GridMakers\Concerns\UseEloquent;
use App\GridMakers\Filter\FilterableKey;
use App\GridMakers\Filter\FilterableKeysDict;
use Illuminate\Database\Eloquent\Model;
use App\Services\Utils\FormatTextService;

class FormsGridMaker implements GridMakable
{
    use UseEloquent;

    /**
     * @var FormatTextService
     */
    private $formatTextService;

    public function __construct(FormatTextService $formatTextService)
    {
        $this->formatTextService = $formatTextService;
    }

    /**
     * @inheritDoc
     */
    protected function baseEloquentQuery(): Builder
    {
        return Form::select([
            'id',
            'name',
            'is_public',
            'description',
            'open_at',
            'close_at',
            'created_at',
            'updated_at',
        ])->with(['answerableTags'])->withoutCustomForms();
    }

    /**
     * @inheritDoc
     */
    public function keys(): array
    {
        return [
            'id',
            'name',
            'is_public',
            'answerableTags',
            'description',
            'open_at',
            'close_at',
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

        if (empty($tags_choices)) {
            $tags_choices = Tag::all()->toArray();
        }

        return new FilterableKeysDict([
            'id' => FilterableKey::number(),
            'name' => FilterableKey::string(),
            'is_public' => FilterableKey::bool(),
            'answerableTags' => FilterableKey::belongsToMany(
                'form_answerable_tags',
                'form_id',
                'tag_id',
                $tags_choices,
                'name'
            ),
            'description' => FilterableKey::string(),
            'open_at' => FilterableKey::datetime(),
            'close_at' => FilterableKey::datetime(),
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
            'name',
            'is_public',
            'description',
            'open_at',
            'close_at',
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
        foreach ($this->keys() as $key) {
            switch ($key) {
                case 'description':
                    $item[$key] = $this->formatTextService->summary(
                        $record->description
                    );
                    break;
                case 'open_at':
                    $item[$key] = !empty($record->open_at) ? $record->open_at->format('Y/m/d H:i:s') : null;
                    break;
                case 'close_at':
                    $item[$key] = !empty($record->close_at) ? $record->close_at->format('Y/m/d H:i:s') : null;
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
        return new Form();
    }
}
