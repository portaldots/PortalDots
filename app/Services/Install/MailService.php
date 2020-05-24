<?php

declare(strict_types=1);

namespace App\Services\Install;

use Jackiedo\DotenvEditor\DotenvEditor;
use Mail;
use App\Mail\Install\TestMailMailable;
use Swift_SmtpTransport;
use Swift_Mailer;

class MailService extends AbstractService
{
    protected function getEnvKeys(): array
    {
        return [
            'MAIL_HOST',
            'MAIL_PORT',
            'MAIL_USERNAME',
            'MAIL_PASSWORD',
            'MAIL_FROM_ADDRESS',
            'MAIL_FROM_NAME',
        ];
    }

    public function getValidationRules(): array
    {
        return [
            'MAIL_HOST' => ['required'],
            'MAIL_PORT' => ['required'],
            'MAIL_USERNAME' => ['required'],
            'MAIL_PASSWORD' => ['required'],
            'MAIL_FROM_ADDRESS' => ['required'],
            'MAIL_FROM_NAME' => ['required'],
        ];
    }

    public function getFormLabels(): array
    {
        return [
            'MAIL_HOST' => 'メールサーバー(SMTP)のホスト',
            'MAIL_PORT' => 'メールサーバー(SMTP)のポート',
            'MAIL_USERNAME' => 'メールユーザー名',
            'MAIL_PASSWORD' => 'メールパスワード',
            'MAIL_FROM_ADDRESS' => 'PortalDots から配信されるメールの差出人メールアドレス',
            'MAIL_FROM_NAME' => 'PortalDots から配信されるメールの差出人の名前',
        ];
    }

    public function sendTestMail(
        Swift_SmtpTransport $transport,
        string $from_address,
        string $from_name
    ) {
        Mail::setSwiftMailer(new Swift_Mailer($transport));
        Mail::to(config('portal.contact_email'))
            ->send(
                (new TestMailMailable($from_address, $from_name))
                    ->subject('PortalDots テストメール')
            );
    }
}
