<?php

namespace App\Http\Controllers\Staff\Circles;

use App\Eloquents\CustomForm;
use App\Http\Controllers\Controller;

class IndexAction extends Controller
{
    public function __invoke()
    {
        return view('staff.circles.index')
            ->with('custom_form', CustomForm::getFormByType('circle'));
    }
}
