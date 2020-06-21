<?php

namespace App\Http\Controllers\Staff\Forms;

use App\Eloquents\Form;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CopyConfirmAction extends Controller
{
    public function __invoke(Form $form)
    {
        return view('staff.forms.copy.index')
            ->with('form', $form);
    }
}
