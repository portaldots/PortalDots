<?php

namespace App\Http\Controllers\Install\Mail;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Install\RunInstallService;

class PostTestAction extends Controller
{
    public function __invoke(Request $request)
    {
        if (empty(session('install_password'))) {
            return redirect()
                ->route('install.mail.edit');
        }

        // プレーンテキストでテストメールが送信される場合、Markdown の * がパスワードの
        // 前後に表示されてしまう。 * も含めて入力された場合、 * は無視して
        // パスワードの比較を行う
        if (session('install_password') !== str_replace('*', '', $request->install_password)) {
            return redirect()
                ->route('install.mail.test')
                ->with('topAlert.type', 'danger')
                ->with('topAlert.title', 'パスワードが違います');
        }
    }
}
