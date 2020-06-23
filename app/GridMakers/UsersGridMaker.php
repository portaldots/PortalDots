<?php

declare(strict_types=1);

namespace App\GridMakers;

use Illuminate\Database\Eloquent\Builder;
use App\Eloquents\User;

class UsersGridMaker extends BaseGridMaker
{
    /**
     * @inheritDoc
     */
    public function query(): Builder
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
            'signed_up_at',
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
            'student_id' => 'string',
            'name_family' => 'string',
            'name_family_yomi' => 'string',
            'name_given' => 'string',
            'name_given_yomi' => 'string',
            'email' => 'string',
            'tel' => 'string',
            'is_staff' => 'bool',
            'is_admin' => 'bool',
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
            'signed_up_at',
            'notes',
            'created_at',
            'updated_at',
        ];
    }
}
