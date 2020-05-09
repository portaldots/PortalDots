<?php

namespace App\Http\Controllers\Install\Mail;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Install\MailService;

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

    public function __invoke(Request $request)
    {
        $this->mailService->updateInfo($request->all());
        try {
            $this->sendTestMail($request->all());
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

    private function sendTestMail(array $config)
    {
        $password = (string)mt_rand(100000, 999999);
        session(['install_password' => $password]);

        config(['host' => $config['MAIL_HOST']]);
        config(['port' => $config['MAIL_PORT']]);
        config(['username' => $config['MAIL_USERNAME']]);
        config(['password' => $config['MAIL_PASSWORD']]);
        config(['from.address' => $config['MAIL_FROM_ADDRESS']]);
        config(['from.name' => $config['MAIL_FROM_NAME']]);

        $this->mailService->sendTestMail($password);
    }
}
