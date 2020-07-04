<?php

namespace App\Http\Controllers\Staff\Circles;

use App\Eloquents\CustomForm;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IndexAction extends Controller
{
    public function __invoke(Request $request)
    {
        return view('staff.circles.index')
            ->with('custom_form', CustomForm::getFormByType('circle'));
    }
}
