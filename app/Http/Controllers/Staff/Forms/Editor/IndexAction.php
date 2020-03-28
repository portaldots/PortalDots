<?php

namespace App\Http\Controllers\Staff\Forms\Editor;

use App\Eloquents\Form;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class IndexAction extends Controller
{
    public function __invoke(int $form_id)
    {
        $form = Form::withoutGlobalScope('withoutCustomForms')->findOrFail($form_id);
        return view('staff.forms.editor')
            ->with('form', $form);
    }
}
