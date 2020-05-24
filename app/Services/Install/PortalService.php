<?php

declare(strict_types=1);

namespace App\Services\Install;

use Jackiedo\DotenvEditor\DotenvEditor;

class PortalService extends AbstractService
{
    protected function getEnvKeys(): array
    {
        return [
            'APP_NAME',
            'APP_URL',
            'PORTAL_ADMIN_NAME',
            'PORTAL_CONTACT_EMAIL',
            'PORTAL_UNIVEMAIL_DOMAIN',
        ];
    }

    public function getValidationRules(): array
    {
        return [
            'APP_NAME' => ['required'],
            'APP_URL' => ['required'],
            'PORTAL_ADMIN_NAME' => ['required'],
            'PORTAL_CONTACT_EMAIL' => ['required'],
            'PORTAL_UNIVEMAIL_DOMAIN' => ['required'],
        ];
    }

    public function getFormLabels(): array
    {
        return [
            'APP_NAME' => 'ポータルの名前',
            'APP_URL' => 'ポータルのURL',
            'PORTAL_ADMIN_NAME' => '実行委員会の名称',
            'PORTAL_CONTACT_EMAIL' => '実行委員会のメールアドレス',
            'PORTAL_UNIVEMAIL_DOMAIN' => '学校発行メールアドレスのドメイン'
        ];
    }
}
