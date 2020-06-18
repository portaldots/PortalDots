<?php

namespace App\Http\Controllers\Circles;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Eloquents\CustomForm;

class CreateAction extends Controller
{
    public function __invoke()
    {
        $this->authorize('circle.create');

        $form = CustomForm::getFormByType('circle');

        $previous = preg_replace('/^:\d+/', '', str_replace(url('/'), '', url()->previous()));

        if (
            isset($form->description)
            && $previous !== route('circles.terms', [], false)
            && $previous !== route('circles.create', [], false)
            && $previous !== route('circles.store', [], false)
        ) {
            return redirect()
                ->route('circles.terms');
        }

        return view('v2.circles.form')
            ->with('form', $form)
            ->with('questions', $form->questions()->get());
    }
}
