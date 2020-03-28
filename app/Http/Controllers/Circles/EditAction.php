<?php

namespace App\Http\Controllers\Circles;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Eloquents\Circle;
use App\Eloquents\CustomForm;

class EditAction extends Controller
{
    public function __invoke(Circle $circle)
    {
        $this->authorize('circle.update', $circle);

        $form = CustomForm::getFormByType('circle');
        return view('v2.circles.form')
            ->with('circle', $circle)
            ->with('form', $form)
            ->with('questions', $form->questions()->get());
    }
}
