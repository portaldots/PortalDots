<?php

namespace App\Http\Controllers\Staff\Forms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Eloquents\Form;
use App\Eloquents\Schedule;

class EditAction extends Controller
{
    public function __invoke(Form $form)
    {
        return view('staff.forms.form')
            ->with('form', $form);
    }
}
