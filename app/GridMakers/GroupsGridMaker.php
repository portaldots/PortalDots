<?php

declare(strict_types=1);

namespace App\GridMakers;

use App\Eloquents\Group;
use Illuminate\Database\Eloquent\Builder;
use App\GridMakers\Concerns\UseEloquent;
use App\GridMakers\Filter\FilterableKey;
use App\GridMakers\Filter\FilterableKeysDict;
use Illuminate\Database\Eloquent\Model;
use App\Services\Utils\FormatTextService;

class GroupsGridMaker implements GridMakable
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
        return Group::select([
            'id',
            'name',
            'name_yomi',
            'notes',
            'created_at',
            'updated_at'
        ])
            ->normal()
            ->with(['owners', 'members'])
            ->withCount(['circles']);
    }

    /**
     * @inheritDoc
     */
    public function keys(): array
    {
        return [
            'id',
            'name',
            'name_yomi',
            'owner',
            'members',
            'circles_count',
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
        $usersType = FilterableKey::belongsToManyWithoutOptions(
            to: 'users',
            pivot: 'group_user',
            foreignPivotKey: 'group_id',
            relatedPivotKey: 'user_id',
            parentKey: 'id',
            parentKeys: new FilterableKeysDict([
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
            ])
        );

        return new FilterableKeysDict([
            'id' => FilterableKey::number(),
            'name' => FilterableKey::string(),
            'name_yomi' => FilterableKey::string(),
            'users' => $usersType,
            'users_count' => FilterableKey::number(),
            'circles_count' => FilterableKey::number(),
            'notes' => FilterableKey::string(),
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
            'name_yomi',
            'circles_count',
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
        $owner = count($record->owners) > 0 ? $record->owners[0] : null;
        $item = [];
        foreach ($this->keys() as $key) {
            switch ($key) {
                case 'owner':
                    $item[$key] = $owner;
                    break;
                case 'members':
                    $item[$key] = $record->members;
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
        return new Group();
    }
}
