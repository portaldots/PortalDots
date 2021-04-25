<?php

declare(strict_types=1);

namespace App\GridMakers;

use App\Eloquents\Permission;
use Illuminate\Database\Eloquent\Builder;
use App\Eloquents\User;
use App\GridMakers\Concerns\UseEloquent;
use App\GridMakers\Filter\FilterableKey;
use App\GridMakers\Filter\FilterableKeysDict;
use Illuminate\Database\Eloquent\Model;

class PermissionsGridMaker implements GridMakable
{
    use UseEloquent;

    /**
     * @inheritDoc
     */
    protected function baseEloquentQuery(): Builder
    {
        return User::select([
            'id',
            'student_id',
            'name_family',
            'name_family_yomi',
            'name_given',
            'name_given_yomi',
            'is_admin',
        ])->with('permissions')->staff();
    }

    /**
     * @inheritDoc
     */
    public function keys(): array
    {
        return [
            'id',
            'name',
            'permissions',
        ];
    }

    /**
     * @inheritDoc
     */
    public function filterableKeys(): FilterableKeysDict
    {
        return new FilterableKeysDict([
            'id' => FilterableKey::number(),
            'student_id' => FilterableKey::string(),
            'name_family' => FilterableKey::string(),
            'name_family_yomi' => FilterableKey::string(),
            'name_given' => FilterableKey::string(),
            'name_given_yomi' => FilterableKey::string(),
            'is_admin' => FilterableKey::bool(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function sortableKeys(): array
    {
        return [
            'id',
            'student_id',
        ];
    }

    /**
     * @inheritDoc
     */
    public function map($record): array
    {
        $keys = [
            'id',
            'student_id',
            'name',
            'permissions',
            'is_admin',
        ];
        $item = [];
        foreach ($keys as $key) {
            switch ($key) {
                case 'permissions':
                    $item[$key] = $record->permissions->map(function ($permission) {
                        return Permission::getDefinedPermissions()[$permission->name];
                    });
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
