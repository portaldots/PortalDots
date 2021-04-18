<?php

namespace App\Http\Controllers\Staff\Forms\Answers;

use App\Eloquents\Form;
use App\Http\Controllers\Controller;

class IndexAction extends Controller
{
    public function __invoke(Form $form)
    {
        return view('staff.forms.answers.index')
            ->with('form', $form);
    }
}
