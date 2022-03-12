<?php

declare(strict_types=1);

namespace App\Services\Install;

use Illuminate\Validation\Rule;

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
            'PORTAL_UNIVEMAIL_LOCAL_PART',
            'PORTAL_UNIVEMAIL_DOMAIN_PART',
            'PORTAL_STUDENT_ID_NAME',
            'PORTAL_UNIVEMAIL_NAME',
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
            'PORTAL_UNIVEMAIL_LOCAL_PART' => ['required', Rule::in(['student_id', 'user_id'])],
            'PORTAL_UNIVEMAIL_DOMAIN_PART' => ['required'],
            'PORTAL_STUDENT_ID_NAME' => ['required'],
            'PORTAL_UNIVEMAIL_NAME' => ['required'],
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
            'PORTAL_STUDENT_ID_NAME' => '個人ごとに割り振られる番号(学籍番号)の呼び方',
            'PORTAL_UNIVEMAIL_NAME' => '学校発行メールアドレスの呼び方',
            'PORTAL_UNIVEMAIL_LOCAL_PART' => '学校発行メールアドレスのローカルパート種別',
            'PORTAL_UNIVEMAIL_DOMAIN_PART' => '学校発行メールアドレスのドメイン',
            'PORTAL_PRIMARY_COLOR_H' => 'アクセントカラー(H)',
            'PORTAL_PRIMARY_COLOR_S' => 'アクセントカラー(S)',
            'PORTAL_PRIMARY_COLOR_L' => 'アクセントカラー(L)',
        ];
    }
}
