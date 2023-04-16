<?php

namespace App\Http\Controllers\Install\Mail;

use Exception;
use Symfony\Component\Mailer\Transport;
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
        try {
            $this->sendTestMail($request->all());
            $this->mailService->updateInfo($request->all());

            return redirect()
                ->route('install.admin.create');
        } catch (Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('topAlert.type', 'danger')
                ->with('topAlert.keepVisible', true)
                ->with('topAlert.title', '設定をご確認ください')
                ->with('topAlert.body', '入力された情報でメールを送信できませんでした。入力内容が正しいかご確認ください : ' . $e->getMessage());
        }
    }

    private function sendTestMail(array $config)
    {
        // PortalDots 4 以前では、メール配信の設定がエラーになる場合、ホストの先頭に `ssl://` をつけることを推奨していた。
        // PortalDots 4 で使っていたメール送信ライブラリー（Swift Mailer）では有効な設定だった。
        // PortalDots 5 以降、メール配信ライブラリーを Symfony Mailer に変更したが、Symfony MailerはTLSを使うかどうかをポート番号で判別している。
        // ホストの先頭に `ssl://` をつけることは無意味となった可能性があるため、文字列置換で `ssl://` を削除している。
        // @see https://github.com/symfony/mailer/blob/42eb71e4bac099cff22fef1b8eae493eb4fd058f/Transport/Smtp/EsmtpTransport.php#L51-L55
        $host = urlencode(str_replace('ssl://', '', $config['MAIL_HOST']));
        $port = $config['MAIL_PORT'];
        $user = urlencode($config['MAIL_USERNAME']);
        $pass = urlencode($config['MAIL_PASSWORD']);

        // @see https://symfony.com/doc/current/mailer.html
        $dsn = "smtp://{$user}:{$pass}@{$host}:{$port}";

        $transport = Transport::fromDsn($dsn);

        $this->mailService->sendTestMail(
            $transport,
            $config['MAIL_FROM_ADDRESS'],
            $config['MAIL_FROM_NAME']
        );
    }
}
