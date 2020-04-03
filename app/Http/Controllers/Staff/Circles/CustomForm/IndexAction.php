<?php

namespace App\Http\Controllers\Staff\Circles\CustomForm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Eloquents\CustomForm;

class IndexAction extends Controller
{
    public function __invoke()
    {
        $form = CustomForm::getFormByType('circle');
        ;
        if (empty($form)) {
            return view('v2.staff.circles.custom_form.index_not_configured');
        }

        return view('v2.staff.circles.custom_form.index')
            ->with('form', $form);
    }
}
