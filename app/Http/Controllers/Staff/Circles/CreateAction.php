<?php

namespace App\Http\Controllers\Staff\Circles;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eloquents\CustomForm;

class CreateAction extends Controller
{
    public function __invoke()
    {
        return view('staff.circles.form')
            ->with('custom_form', CustomForm::getFormByType('circle'));
    }
}
