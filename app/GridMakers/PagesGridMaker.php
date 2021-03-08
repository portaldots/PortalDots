<?php

declare(strict_types=1);

namespace App\GridMakers;

use App\Eloquents\Tag;
use Illuminate\Database\Eloquent\Builder;
use App\Eloquents\Page;
use App\GridMakers\Concerns\UseEloquent;
use App\GridMakers\Filter\FilterableKey;
use App\GridMakers\Filter\FilterableKeysDict;
use Illuminate\Database\Eloquent\Model;
use App\Services\Utils\FormatTextService;

class PagesGridMaker implements GridMakable
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
        return Page::select([
            'id',
            'title',
            'body',
            'notes',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by'
        ])->with(['viewableTags', 'userCreatedBy', 'userUpdatedBy']);
    }

    /**
     * @inheritDoc
     */
    public function keys(): array
    {
        return [
            'id',
            'title',
            'viewableTags',
            'body',
            'notes',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by'
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

        $users_type = FilterableKey::belongsTo(
            'users',
            new FilterableKeysDict([
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
                'updated_at' => FilterableKey::datetime()
            ])
        );

        return new FilterableKeysDict([
            'id' => FilterableKey::number(),
            'title' => FilterableKey::string(),
            'viewableTags' => FilterableKey::belongsToMany(
                'page_viewable_tags',
                'page_id',
                'tag_id',
                $tags_choices,
                'name'
            ),
            'body' => FilterableKey::string(),
            'notes' => FilterableKey::string(),
            'created_at' => FilterableKey::datetime(),
            'created_by' => $users_type,
            'updated_at' => FilterableKey::datetime(),
            'updated_by' => $users_type
        ]);
    }

    /**
     * @inheritDoc
     */
    public function sortableKeys(): array
    {
        return [
            'id',
            'title',
            'body',
            'notes',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by'
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
                case 'body':
                    $item[$key] = $this->formatTextService->summary(
                        $record->body
                    );
                    break;
                case 'created_at':
                    $item[$key] = !empty($record->created_at) ? $record->created_at->format('Y/m/d H:i:s') : null;
                    break;
                case 'created_by':
                    $item[$key] = $record->userCreatedBy;
                    break;
                case 'updated_at':
                    $item[$key] = !empty($record->updated_at) ? $record->updated_at->format('Y/m/d H:i:s') : null;
                    break;
                case 'updated_by':
                    $item[$key] = $record->userUpdatedBy;
                    break;
                default:
                    $item[$key] = $record->$key;
            }
        }
        return $item;
    }

    protected function model(): Model
    {
        return new Page();
    }
}
