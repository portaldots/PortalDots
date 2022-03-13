<?php

namespace App\Exceptions;

use PDOException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof PDOException && !config('app.debug')) {
            // データベース接続エラー
            //
            // そのまま Blade ファイルによるエラーページを表示してしまうと、
            // Blade ファイル内からデータベース接続が行われ、エラーページを
            // 正常に表示することができない。そのため、Blade ファイルを使わず
            // にエラーを表示する。
            //
            // このエラーが表示される状況の例は以下の通り。
            //  1. データベース設定が間違っている
            //  2. 接続先のデータベースにPortalDotsで利用するテーブルがない
            //     →データベース内のデータを全削除した上でテーブルを作り直す
            //       コマンド : php artisan migrate:refresh
            $app_name = config('app.name');
            return response("
                <!doctype html>
                <meta charset=\"utf-8\">
                <title>データベース接続エラー</title>
                <div style=\"text-align: center\">
                    <h1>データベースと接続できません</h1>
                    <hr>
                    <p>設定ファイル(.env)内のデータベース設定が正しいかご確認ください。</p>
                    <hr>
                    <p>{$app_name} • Powered by PortalDots</p>
                </div>");
        }

        $response = parent::render($request, $exception);

        // ステータスコードがエラーとなるページへのアクセスは Turbolinks に
        // 対応していないので、200 を返す
        if (
            !empty($request->headers->get('Turbolinks-Referrer'))
            && in_array($response->getStatusCode(), [403, 404, 500, 503], true)
        ) {
            $response->setStatusCode(200);
        }

        return $response;
    }
}
