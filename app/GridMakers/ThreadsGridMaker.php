<?php

declare(strict_types=1);

namespace App\GridMakers;

use App\Eloquents\Thread;
use Illuminate\Database\Eloquent\Builder;
use App\GridMakers\Concerns\UseEloquent;
use App\GridMakers\Filter\FilterableKey;
use App\GridMakers\Filter\FilterableKeysDict;
use Illuminate\Database\Eloquent\Model;

class ThreadsGridMaker implements GridMakable
{
    use UseEloquent;

    /**
     * @inheritDoc
     */
    protected function baseEloquentQuery(): Builder
    {
        return Thread::select($this->keys())->with(['circle', 'user', 'assignee']);
    }

    /**
     * @inheritDoc
     */
    public function keys(): array
    {
        return [
            'id',
            'circle_id',
            'user_id',
            'status',
            'assignee_id',
            'last_entry_at',
            'created_at',
        ];
    }

    /**
     * @inheritDoc
     */
    public function filterableKeys(): FilterableKeysDict
    {
        return new FilterableKeysDict([
            'id' => FilterableKey::number(),
            'circle_id' => FilterableKey::belongsTo('circles', new FilterableKeysDict([
                'id' => FilterableKey::number(),
                'name' => FilterableKey::string(),
                'group_name' => FilterableKey::string(),
            ])),
            'user_id' => FilterableKey::belongsTo('users', new FilterableKeysDict([
                'id' => FilterableKey::number(),
                'name_family' => FilterableKey::string(),
                'name_given' => FilterableKey::string(),
            ])),
            'status' => FilterableKey::enum([
                Thread::STATUS_NEEDS_STAFF,
                Thread::STATUS_AWAITING_REPLY,
                Thread::STATUS_RESOLVED,
            ]),
            'assignee_id' => FilterableKey::belongsTo('users', new FilterableKeysDict([
                'id' => FilterableKey::number(),
                'name_family' => FilterableKey::string(),
                'name_given' => FilterableKey::string(),
            ])),
            'last_entry_at' => FilterableKey::datetime(),
            'created_at' => FilterableKey::datetime(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function sortableKeys(): array
    {
        return [
            'id',
            'status',
            'last_entry_at',
            'created_at',
        ];
    }

    /**
     * @inheritDoc
     */
    public function defaultOrderBy(): string
    {
        return 'last_entry_at';
    }

    /**
     * @inheritDoc
     */
    public function defaultDirection(): string
    {
        return 'desc';
    }

    /**
     * @inheritDoc
     */
    public function map($record): array
    {
        $item = [];
        foreach ($this->keys() as $key) {
            switch ($key) {
                case 'circle_id':
                    $item[$key] = isset($record->circle) ? $record->circle->only(['id', 'name', 'group_name']) : null;
                    break;
                case 'user_id':
                    $item[$key] = isset($record->user)
                        ? ['id' => $record->user->id, 'name' => $record->user->name]
                        : null;
                    break;
                case 'assignee_id':
                    $item[$key] = isset($record->assignee)
                        ? ['id' => $record->assignee->id, 'name' => $record->assignee->name]
                        : null;
                    break;
                case 'last_entry_at':
                    $item[$key] = !empty($record->last_entry_at) ? $record->last_entry_at->format('Y/m/d H:i:s') : null;
                    break;
                case 'created_at':
                    $item[$key] = !empty($record->created_at) ? $record->created_at->format('Y/m/d H:i:s') : null;
                    break;
                default:
                    $item[$key] = $record->$key;
            }
        }
        return $item;
    }

    protected function model(): Model
    {
        return new Thread();
    }
}
