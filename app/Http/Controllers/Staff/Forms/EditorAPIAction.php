<?php

namespace App\Http\Controllers\Staff\Forms;

use App\Eloquents\Form;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EditorAPIAction extends Controller
{
    public function __invoke(Form $form)
    {
        abort(404);

        /*
         * サーバーでやってほしい処理
         *
         * - Questions は priority による並び替えがされた状態にする
         */
    }
}
