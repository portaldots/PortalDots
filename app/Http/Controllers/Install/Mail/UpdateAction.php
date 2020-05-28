<?php

namespace App\Http\Controllers\Install\Mail;

use App\Http\Controllers\Controller;
use App\Http\Requests\Install\MailRequest;
use App\Services\Install\MailService;
use Swift_TransportException;
use Swift_SmtpTransport;

class UpdateAction extends Controller
{
    /**
     * @var MailService
     */
    private $mailService;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    public function __invoke(MailRequest $request)
    {
        try {
            $this->sendTestMail($request->all());
            $this->mailService->updateInfo($request->all());

            return redirect()
                ->route('install.admin.create');
        } catch (Swift_TransportException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('topAlert.type', 'danger')
                ->with('topAlert.title', '設定をご確認ください')
                ->with('topAlert.body', '入力された情報でメールを送信できませんでした。入力内容が正しいかご確認ください');
        }
    }

    private function sendTestMail(array $config)
    {
        $transport = new Swift_SmtpTransport($config['MAIL_HOST'], $config['MAIL_PORT']);
        $transport->setUsername($config['MAIL_USERNAME']);
        $transport->setPassword($config['MAIL_PASSWORD']);

        $this->mailService->sendTestMail(
            $transport,
            $config['MAIL_FROM_ADDRESS'],
            $config['MAIL_FROM_NAME']
        );
    }
}
