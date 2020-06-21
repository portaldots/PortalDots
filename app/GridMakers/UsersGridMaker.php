<?php

declare(strict_types=1);

namespace App\GridMakers;

use App\Eloquents\User;

class UsersGridMaker extends BaseGridMaker
{
    /**
     * @inheritDoc
     */
    public function view(): string
    {
        return 'staff.users.index';
    }

    /**
     * @inheritDoc
     */
    public function query()
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
            'is_verified_by_staff',
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
        return [];
    }

    /**
     * @inheritDoc
     */
    public function sortableKeys(): array
    {
        return [];
    }
}
