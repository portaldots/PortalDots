<?php

namespace App\Http\Controllers\Install\Mail;

use Exception;
use App\Http\Controllers\Controller;
use App\Services\Install\MailService;

class SendTestAction extends Controller
{
    /**
     * @var MailService
     */
    private $mailService;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    public function __invoke()
    {
        try {
            $this->sendTestMail();

            return response()->json([
                'message' => '',
            ], 200);
        } catch (Exception $e) {
            // エラー内容をJSONで返す
            return response()->json([
                'message' => 'テストメールを送信できませんでした。入力内容が正しいかご確認ください。 : ' . $e->getMessage(),
            ], 500);
        }
    }

    private function sendTestMail()
    {
        $this->mailService->sendTestMail();
    }
}
