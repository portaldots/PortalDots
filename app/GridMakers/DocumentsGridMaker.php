<?php

declare(strict_types=1);

namespace App\GridMakers;

use Illuminate\Database\Eloquent\Builder;
use App\Eloquents\Document;
use App\GridMakers\Concerns\UseEloquent;

class DocumentsGridMaker implements GridMakable
{
    use UseEloquent;

    /**
     * @inheritDoc
     */
    public function baseEloquentQuery(): Builder
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
    public function filterableKeys(): array
    {
        return [
            'id' => 'number',
            'name' => 'string',
            'schedule_id' => 'number',  // TODO: リファレンス型みたいなものを導入したい
            'description' => 'string',
            'is_public' => 'bool',
            'is_important' => 'bool',
            'created_at' => 'datetime',
            'created_by' => 'number',     // TODO: リファレンス型みたいなものを導入したい
            'updated_at' => 'datetime',
            'updated_by' => 'number',   // TODO: リファレンス型みたいなものを導入したい
            'notes' => 'string',
        ];
    }

    /**
     * @inheritDoc
     */
    public function sortableKeys(): array
    {
        return [
            'id',
            'name',
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
                case 'schedule_id':
                    $item[$key] = $record->schedule;
                    break;
                case 'created_by':
                    $item[$key] = $record->userCreatedBy;
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
}
