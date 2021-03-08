<?php

declare(strict_types=1);

namespace App\GridMakers;

use Illuminate\Database\Eloquent\Builder;
use App\Eloquents\Document;
use App\GridMakers\Concerns\UseEloquent;
use App\GridMakers\Filter\FilterableKey;
use App\GridMakers\Filter\FilterableKeysDict;
use Illuminate\Database\Eloquent\Model;

class DocumentsGridMaker implements GridMakable
{
    use UseEloquent;

    /**
     * @inheritDoc
     */
    protected function baseEloquentQuery(): Builder
    {
        return Document::select($this->keys())->with(['schedule', 'userCreatedBy', 'userUpdatedBy']);
    }

    /**
     * @inheritDoc
     */
    public function keys(): array
    {
        return [
            'id',
            'name',
            'path',
            'size',
            'extension',
            'schedule_id',
            'description',
            'is_public',
            'is_important',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
            'notes',
        ];
    }

    /**
     * @inheritDoc
     */
    public function filterableKeys(): FilterableKeysDict
    {
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

        return new FilterableKeysDict([
            'id' => FilterableKey::number(),
            'name' => FilterableKey::string(),
            'size' => FilterableKey::number(),
            'extension' => FilterableKey::string(),
            'schedule_id' => FilterableKey::belongsTo('schedules', new FilterableKeysDict([
                'id' => FilterableKey::number(),
                'name' => FilterableKey::string(),
                'start_at' => FilterableKey::datetime(),
                'place' => FilterableKey::string(),
                'notes' => FilterableKey::string(),
                'created_at' => FilterableKey::datetime(),
                'updated_at' => FilterableKey::datetime(),
            ])),
            'description' => FilterableKey::string(),
            'is_public' => FilterableKey::bool(),
            'is_important' => FilterableKey::bool(),
            'created_at' => FilterableKey::datetime(),
            'created_by' => $users_type,
            'updated_at' => FilterableKey::datetime(),
            'updated_by' => $users_type,
            'notes' => FilterableKey::string(),
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
            'size',
            'extension',
            'schedule_id',
            'description',
            'is_public',
            'is_important',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
            'notes',
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
                case 'extension':
                    $item[$key] = mb_strtoupper($record->extension);
                    break;
                case 'schedule_id':
                    $item[$key] = $record->schedule;
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
        return new Document();
    }
}
