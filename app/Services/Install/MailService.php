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
