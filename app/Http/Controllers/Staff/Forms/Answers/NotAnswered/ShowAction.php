<?php

namespace App\Http\Controllers\Staff\Forms\Answers\NotAnswered;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eloquents\Form;

class ShowAction extends Controller
{
    public function __invoke(Form $form)
    {
        if (isset($form->customForm)) {
            abort(404);
        }

        $circles = $form->notAnswered();

        return view('v2.staff.forms.answers.notanswered.index')
            ->with('form', $form)
            ->with('circles', $circles);
    }
}
