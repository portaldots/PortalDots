<?php

declare(strict_types=1);

namespace App\Services\Emails;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;
use App\Eloquents\User;
use App\Eloquents\Email;
use App\Mail\Emails\SendEmailServiceMailable;

/**
 * メールを一斉送信するためのサービスクラス
 */
class SendEmailService
{
    private const NUMBERS_PER_EXECUTE = 60; // 1回の処理で取得するメールレコード数
    private const SEND_INTERVAL_SEC = 1; // メール送信間隔(秒)
    private const SEC_PER_JOB = 60; // プログラム強制終了まで
    private const NUM_RETRY_PER_EMAIL_ADDRESS = 3; // 送信失敗した場合，リトライする回数
    private const NUM_RETRY_PER_JOB = 10; // １回のジョブで繰り返す回数

    public static function bulkEnqueue(string $subject, string $body, Collection $users_email_to)
    {
        $created_at = now();
        Email::insert($users_email_to->map(function (User $user) use ($subject, $body, $created_at) {
            return [
                'subject' => $subject,
                'body' => $body,
                'email_to' => $user->email,
                'email_to_name' => $user->name,
                'created_at' => $created_at,
            ];
        })->toArray());
    }

    public static function runJob()
    {
        $start_time = time(); // 開始時刻(UNIXエポック) (秒)

        // 今期失敗回数
        // (この関数が呼び出されて以降失敗した回数)
        $count_failed_now = 0;

        $emails = Email::orderBy('id', 'ASC')
                            ->whereNull('locked_at') // 排他ロックされていない
                            ->whereNull('sent_at')   // かつ，未送信
                            ->where('count_failed', '<', self::NUM_RETRY_PER_EMAIL_ADDRESS) // かつ，リトライ上限に達していないレコード
                            ->take(self::NUMBERS_PER_EXECUTE)
                            ->get();           // 最新 self::NUMBERS_PER_EXECUTE 件を取得
        foreach ($emails as $email) {
            $now = now();

            // ロック処理
            $email->locked_at = $now;
            $email->save();

            try {
                self::sendEmail(
                    $email->subject,
                    $email->body,
                    $email->email_to,
                    $email->email_to_name
                );

                // 送信済みフラグセット
                $email->sent_at = $now;
                $email->locked_at = null;
                $email->save();
            } catch (Exception $e) {
                // 送信失敗したので失敗カウントを1つ追加
                ++$email->count_failed;
                ++$count_failed_now;

                // TODO: エラーの内容をログに残せたら良さそう

                // ロック解除
                $email->locked_at = null;
                $email->save();

                // エラーになった場合、次回CRONが起動した時に送信再試行する
            }

            // 現在実行中の runJob で、失敗回数が self::NUM_RETRY_PER_JOB 回を超えたら
            // サーバー側の設定ミスなどが考えられるので、処理を中止する
            if ($count_failed_now > self::NUM_RETRY_PER_JOB) {
                // TODO: 管理者にメールするなりログに書き込むなりする...

                // とりあえず強制終了
                break;
            }

            // 強制終了するか否か
            if (time() - $start_time > self::SEC_PER_JOB) {
                break;
            }

            // スリープ
            sleep(self::SEND_INTERVAL_SEC);
        }
    }

    private static function sendEmail(string $subject, string $body, string $email_to, string $email_to_name)
    {
        $recipient = new \stdClass();
        $recipient->email = $email_to;
        $recipient->name = $email_to_name;
        Mail::to($recipient)
            ->send(new SendEmailServiceMailable($subject, $body));
    }
}
