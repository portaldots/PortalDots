<?php

declare(strict_types=1);

namespace App\GridMakers;

use Illuminate\Database\Eloquent\Builder;
use App\Eloquents\Document;

class DocumentsGridMaker extends BaseGridMaker
{
    /**
     * @inheritDoc
     */
    public function query(): Builder
    {
        return Document::select($this->keys());
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
}
