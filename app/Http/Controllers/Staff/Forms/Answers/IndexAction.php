<?php

namespace App\Http\Controllers\Staff\Forms\Answers;

use App\Eloquents\Form;
use App\Http\Controllers\Controller;

class IndexAction extends Controller
{
    public function __invoke(Form $form)
    {
        if (isset($form->customForm) && $form->customForm->type === 'circle') {
            // このフォームが企画参加登録フォームの場合、企画情報管理ページへリダイレクト
            return redirect()->route('staff.circles.index');
        }

        return view('staff.forms.answers.index')
            ->with('form', $form);
    }
}
