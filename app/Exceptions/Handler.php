<?php

namespace App\Exceptions;

use Exception;
use PDOException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof PDOException) {
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
            //
            // (CodeIgniterを完全に廃止した場合、以下のコメントは削除する)
            // 同じHTMLコードが
            //  application/views/errors/html/error_db.php
            // にも書かれている。このファイルを修正する際は、
            //  application/views/errors/html/error_db.php
            // に書かれている同様のHTMLコードも修正すること。
            return response('
                <!doctype html>
                <meta charset="utf-8">
                <title>データベース接続エラー</title>
                <div style="text-align: center">
                    <h1>データベースと接続できません</h1>
                    <hr>
                    <p>設定ファイル(.env)内のデータベース設定が正しいかご確認ください。</p>
                    <hr>
                    <p>Powered by PortalDots</p>
                </div>');
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
