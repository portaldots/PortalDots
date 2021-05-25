<?php

declare(strict_types=1);

namespace App\Services\Install;

class PortalService extends AbstractService
{
    protected function getEnvKeys(): array
    {
        return [
            'APP_NAME',
            'APP_URL',
            'APP_FORCE_HTTPS',
            'PORTAL_ADMIN_NAME',
            'PORTAL_CONTACT_EMAIL',
            'PORTAL_UNIVEMAIL_DOMAIN',
            'PORTAL_PRIMARY_COLOR_H',
            'PORTAL_PRIMARY_COLOR_S',
            'PORTAL_PRIMARY_COLOR_L',
        ];
    }

    public function getValidationRules(): array
    {
        return [
            'APP_NAME' => ['required'],
            'APP_URL' => ['required'],
            'APP_FORCE_HTTPS' => ['nullable', 'boolean'],
            'PORTAL_ADMIN_NAME' => ['required'],
            'PORTAL_CONTACT_EMAIL' => ['required'],
            'PORTAL_UNIVEMAIL_DOMAIN' => ['required'],
            'PORTAL_PRIMARY_COLOR_H' => ['required', 'integer', 'min:0', 'max:360'],
            'PORTAL_PRIMARY_COLOR_S' => ['required', 'integer', 'min:0', 'max:100'],
            'PORTAL_PRIMARY_COLOR_L' => ['required', 'integer', 'min:0', 'max:100'],
        ];
    }

    public function getFormLabels(): array
    {
        return [
            'APP_NAME' => 'ポータルの名前',
            'APP_URL' => 'ポータルのURL',
            'APP_FORCE_HTTPS' => 'https接続を強制する',
            'PORTAL_ADMIN_NAME' => '実行委員会の名称',
            'PORTAL_CONTACT_EMAIL' => '実行委員会のメールアドレス',
            'PORTAL_UNIVEMAIL_DOMAIN' => '学校発行メールアドレスのドメイン',
            'PORTAL_PRIMARY_COLOR_H' => 'テーマカラー(H)',
            'PORTAL_PRIMARY_COLOR_S' => 'テーマカラー(S)',
            'PORTAL_PRIMARY_COLOR_L' => 'テーマカラー(L)',
        ];
    }
}
