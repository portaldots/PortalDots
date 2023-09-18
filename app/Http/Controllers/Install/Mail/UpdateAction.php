<?php

namespace App\Http\Controllers\Install\Mail;

use Exception;
use App\Http\Controllers\Controller;
use App\Http\Requests\Install\MailRequest;
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

    public function __invoke(MailRequest $request)
    {
        $config = $request->all();

        try {
            $this->mailService->updateInfo([
                // PortalDots 4 以前では、メール配信の設定がエラーになる場合、ホストの先頭に `ssl://` をつけることを推奨していた。
                // PortalDots 4 で使っていたメール送信ライブラリー（Swift Mailer）では有効な設定だった。
                // PortalDots 5 以降、メール配信ライブラリーを Symfony Mailer に変更したが、Symfony MailerはTLSを使うかどうかをポート番号で判別している。
                // ホストの先頭に `ssl://` をつけることは無意味となった可能性があるため、文字列置換で `ssl://` を削除している。
                // @see https://github.com/symfony/mailer/blob/42eb71e4bac099cff22fef1b8eae493eb4fd058f/Transport/Smtp/EsmtpTransport.php#L51-L55
                'MAIL_HOST' => urlencode(str_replace('ssl://', '', $config['MAIL_HOST'])),
                'MAIL_PORT' => $config['MAIL_PORT'],
                'MAIL_USERNAME' => $config['MAIL_USERNAME'],
                'MAIL_PASSWORD' => $config['MAIL_PASSWORD'],
                'MAIL_FROM_ADDRESS' => $config['MAIL_FROM_ADDRESS'],
                'MAIL_FROM_NAME' => $config['MAIL_FROM_NAME'],
            ]);

            return response()->json([
                'message' => '',
            ], 200);
        } catch (Exception $e) {
            // エラー内容をJSONで返す
            return response()->json([
                'message' => 'メール配信の設定を保存できませんでした。 : ' . $e->getMessage(),
            ], 500);
        }
    }
}
