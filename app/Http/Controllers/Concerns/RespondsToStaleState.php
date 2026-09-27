<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

/**
 * 楽観ロック（lock_version）の不一致発生時のレスポンスを生成する。通常の
 * リクエストの場合は元の画面へ入力内容を保持したまま戻し、JSON/APIリクエスト
 * の場合は HTTP 409 を返す
 */
trait RespondsToStaleState
{
    private function staleStateResponse(Request $request, string $title, string $body, string $jsonMessage)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $jsonMessage], 409);
        }

        return back()
            ->withInput()
            ->with('topAlert.type', 'danger')
            ->with('topAlert.title', $title)
            ->with('topAlert.body', $body);
    }
}
