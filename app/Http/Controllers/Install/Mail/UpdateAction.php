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
        try {
            $this->sendTestMail();
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('topAlert.type', 'danger')
                ->with('topAlert.title', '設定をご確認ください')
                ->with('topAlert.body', '入力された情報でメールを送信できませんでした。入力内容が正しいかご確認ください');
        }

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
