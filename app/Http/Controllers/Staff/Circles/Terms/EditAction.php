<?php

namespace App\Http\Controllers\Staff\Circles\Terms;

use App\Eloquents\CustomForm;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EditAction extends Controller
{
    public function __invoke()
    {
        $form = CustomForm::getFormByType('circle');

        if (empty($form)) {
            abort(404);
        }

        return view('v2.staff.circles.terms')
            ->with('description', $form->description);
    }
}
