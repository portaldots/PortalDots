<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChangeLocaleAction extends Controller
{
    public function __invoke()
    {
        // 実際の言語切り替え処理は Localization ミドルウェアが行うため
        // このアクションでは、単に前の URL へのリダイレクトのみ行う
        return redirect()
            ->back();
    }
}
