<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

/**
 * lock_version の不一致(StaleAnswerException)発生時のレスポンスを
 * 生成する。通常のリクエストの場合は元の画面へ入力内容を保持したまま
 * 戻し、JSON/APIリクエストの場合は HTTP 409 を返す
 */
trait RespondsToStaleAnswer
{
    use RespondsToStaleState;

    private function staleAnswerResponse(Request $request)
    {
        return $this->staleStateResponse(
            $request,
            '回答が別の操作によって更新されています',
            '画面を再読み込みしてから、もう一度お試しください',
            '回答が別の操作によって更新されているため、この操作を行えません。画面を再読み込みしてください。'
        );
    }
}
