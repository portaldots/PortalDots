<?php

namespace App\Http\Controllers\Staff\Forms\Editor;

use App\Eloquents\Form;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FrameAction extends Controller
{
    public function __invoke(Form $form)
    {
        return view('staff.forms.editor_frame')
            ->with('form', $form);
    }
}
