<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

/**
 * lock_version の不一致(StaleThreadException)発生時のレスポンスを生成する。
 * 通常のリクエストの場合は元の画面へ入力内容を保持したまま戻し、JSON/APIリクエスト
 * の場合は HTTP 409 を返す
 */
trait RespondsToStaleThread
{
    use RespondsToStaleState;

    private function staleThreadResponse(Request $request)
    {
        return $this->staleStateResponse(
            $request,
            '会話が別の操作によって更新されています',
            '画面を再読み込みしてから、もう一度お試しください',
            '会話が別の操作によって更新されているため、この操作を行えません。画面を再読み込みしてください。'
        );
    }
}
