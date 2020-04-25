<?php

namespace App\Http\Controllers\Install\Mail;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Install\MailService;
use Config;

class UpdateAction extends Controller
{
    /**
     * @var MailService
     */
    private $editor;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    public function __invoke(Request $request)
    {
        $this->mailService->updateInfo($request->all());
        $this->sendTestMail();
        return redirect()
            ->route('install.mail.test');
    }

    private function sendTestMail()
    {
        $password = (string)mt_rand(100000, 999999);
        session(['install_password' => $password]);

        Config::set('host', 'MAIL_HOST');
        Config::set('port', 'MAIL_PORT');
        Config::set('username', 'MAIL_USERNAME');
        Config::set('password', 'MAIL_PASSWORD');
        Config::set('from.address', 'MAIL_FROM_ADDRESS');
        Config::set('from.name', 'MAIL_FROM_NAME');

        $this->mailService->sendTestMail($password);
    }
}
