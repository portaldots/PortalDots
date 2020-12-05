<?php

declare(strict_types=1);

namespace App\GridMakers;

use Illuminate\Database\Eloquent\Builder;
use App\Eloquents\Tag;
use App\GridMakers\Concerns\UseEloquent;
use App\GridMakers\Filter\FilterableKey;
use App\GridMakers\Filter\FilterableKeysDict;
use Illuminate\Database\Eloquent\Model;
use App\Services\Utils\FormatTextService;

class TagsGridMaker implements GridMakable
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
        return Tag::select([
            'id',
            'name',
            'created_at',
            'updated_at',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function keys(): array
    {
        return [
            'id',
            'name',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * @inheritDoc
     */
    public function filterableKeys(): FilterableKeysDict
    {
        return new FilterableKeysDict([
            'id' => FilterableKey::number(),
            'name' => FilterableKey::string(),
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
                case 'created_at':
                    $item[$key] = $record->created_at->format('Y/m/d H:i:s');
                    break;
                case 'updated_at':
                    $item[$key] = $record->updated_at->format('Y/m/d H:i:s');
                    break;
                default:
                    $item[$key] = $record->$key;
            }
        }
        return $item;
    }

    protected function model(): Model
    {
        return new Tag();
    }
}
