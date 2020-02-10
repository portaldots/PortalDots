<?php

namespace App\Http\Controllers\Forms\Answers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eloquents\Form;

class CreateAction extends Controller
{
    public function __invoke(Form $form)
    {
        $questions = $form->questions()->get();
        return view('v2.forms.answers.form')
            ->with('form', $form)
            ->with('questions', $questions);
    }
}
