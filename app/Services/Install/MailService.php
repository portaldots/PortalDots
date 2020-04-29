<?php

declare(strict_types=1);

namespace App\Services\Install;

use Jackiedo\DotenvEditor\DotenvEditor;
use Mail;
use App\Mail\Install\TestMailMailable;

class MailService extends AbstractService
{
    public function getEnvKeys(): array
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

    public function sendTestMail(string $password)
    {
        Mail::to(config('portal.contact_email'))
            ->send(
                (new TestMailMailable(
                    $password
                ))
                    ->subject('PortalDots テストメール')
            );
    }
}
