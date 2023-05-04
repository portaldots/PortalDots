<?php

namespace App\Http\Controllers\Staff\Forms\Answers;

use App\Eloquents\Answer;
use App\Http\Controllers\Controller;
use App\Eloquents\Form;

class DestroyAction extends Controller
{
    public function __invoke(Form $form, Answer $answer)
    {
        $answer->delete();
        return redirect()
            ->route('staff.forms.answers.index', ['form' => $form])
            ->with('topAlert.title', '回答を削除しました');
    }
}
