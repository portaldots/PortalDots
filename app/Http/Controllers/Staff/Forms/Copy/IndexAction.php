<?php

namespace App\Http\Controllers\Staff\Forms\Copy;

use App\Eloquents\Form;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IndexAction extends Controller
{
    public function __invoke(Form $form)
    {
        return view('v2.staff.forms.copy.index')
            ->with('form', $form);
    }
}
