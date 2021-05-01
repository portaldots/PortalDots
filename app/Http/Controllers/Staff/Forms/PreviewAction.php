<?php

namespace App\Http\Controllers\Staff\Forms;

use App\Eloquents\Form;
use App\Http\Controllers\Controller;

class PreviewAction extends Controller
{
    public function __invoke(Form $form)
    {
        return view('staff.forms.preview')
            ->with('form', $form)
            ->with('questions', $form->questions()->get());
    }
}
