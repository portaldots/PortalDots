<?php

namespace App\Http\Controllers\Staff\Forms\Answers\NotAnswered;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eloquents\Form;

class ShowAction extends Controller
{
    public function __invoke(Form $form)
    {
        $circles = $form->notAnswered();

        return view('v2.staff.forms.answers.notanswered.index')
            ->with('form', $form)
            ->with('circles', $circles);
    }
}
