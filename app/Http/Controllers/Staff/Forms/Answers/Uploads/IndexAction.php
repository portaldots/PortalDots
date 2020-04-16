<?php

namespace App\Http\Controllers\Staff\Forms\Answers\Uploads;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eloquents\Form;

class IndexAction extends Controller
{
    public function __invoke(Form $form)
    {
        return view('v2.staff.forms.answers.uploads.index')
            ->with('form', $form);
    }
}
