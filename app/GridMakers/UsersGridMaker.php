<?php

declare(strict_types=1);

namespace App\GridMakers;

use Illuminate\Database\Eloquent\Builder;
use App\Eloquents\User;
use App\GridMakers\Concerns\UseEloquent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class UsersGridMaker implements GridMakable
{
    use UseEloquent;

    /**
     * @inheritDoc
     */
    protected function baseEloquentQuery(): Builder
    {
        return User::select($this->keys());
    }

    /**
     * @inheritDoc
     */
    public function keys(): array
    {
        return [
            'id',
            'student_id',
            'name_family',
            'name_family_yomi',
            'name_given',
            'name_given_yomi',
            'email',
            'tel',
            'is_staff',
            'is_admin',
            'email_verified_at',
            'univemail_verified_at',
            'last_accessed_at',
            'notes',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * @inheritDoc
     */
    public function filterableKeys(): array
    {
        return [
            'id' => ['type' => 'number'],
            'student_id' => ['type' => 'string'],
            'name_family' => ['type' => 'string'],
            'name_family_yomi' => ['type' => 'string'],
            'name_given' => ['type' => 'string'],
            'name_given_yomi' => ['type' => 'string'],
            'email' => ['type' => 'string'],
            'tel' => ['type' => 'string'],
            'is_staff' => ['type' => 'bool'],
            'is_admin' => ['type' => 'bool'],
            'email_verified_at' => ['type' => 'isNull'],
            'univemail_verified_at' => ['type' => 'isNull'],
            'notes' => ['type' => 'string'],
            'created_at' => ['type' => 'datetime'],
            'updated_at' => ['type' => 'datetime'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function sortableKeys(): array
    {
        return [
            'id',
            'student_id',
            'name_family',
            'name_family_yomi',
            'name_given',
            'name_given_yomi',
            'email',
            'tel',
            'is_staff',
            'is_admin',
            'email_verified_at',
            'univemail_verified_at',
            'last_accessed_at',
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
        $item = [];
        foreach ($this->keys() as $key) {
            switch ($key) {
                case 'last_accessed_at':
                    $item[$key] = $record->formatLastAccessedAt();
                    break;
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
        return new User();
    }
}
