<?php

declare(strict_types=1);

namespace App\Services\Install;

use Jackiedo\DotenvEditor\DotenvEditor;

class PortalService extends AbstractService
{
    public function getEnvKeys(): array
    {
        return [
            'APP_NAME',
            'APP_URL',
            'PORTAL_ADMIN_NAME',
            'PORTAL_CONTACT_EMAIL',
            'PORTAL_UNIVEMAIL_DOMAIN',
        ];
    }
}
