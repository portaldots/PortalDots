<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

/**
 * lock_version または対象の版の不一致(StaleDocumentApprovalException)
 * 発生時のレスポンスを生成する。通常のリクエストの場合は元の画面へ入力内容を
 * 保持したまま戻し、JSON/APIリクエストの場合は HTTP 409 を返す
 */
trait RespondsToStaleDocumentApproval
{
    use RespondsToStaleState;

    private function staleDocumentApprovalResponse(Request $request)
    {
        return $this->staleStateResponse(
            $request,
            '確認依頼が別の操作によって更新されています',
            '画面を再読み込みしてから、もう一度お試しください',
            '確認依頼が別の操作によって更新されているため、この操作を行えません。画面を再読み込みしてください。'
        );
    }
}
