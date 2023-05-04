<?php

namespace App\Http\Controllers\Staff\Forms\Answers;

use App\Eloquents\Form;
use App\Http\Controllers\Controller;

class IndexAction extends Controller
{
    public function __invoke(Form $form)
    {
        if (isset($form->participationType)) {
            // このフォームが企画参加登録フォームの場合、企画情報管理ページへリダイレクト
            return redirect()->route('staff.circles.participation_types.index', [
                'participation_type' => $form->participationType
            ]);
        }

        return view('staff.forms.answers.index')
            ->with('form', $form);
    }
}
