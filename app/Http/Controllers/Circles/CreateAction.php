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

        if (
            isset($form->description)
            && url()->previous() !== config('app.url') . route('circles.terms', [], false)
            && url()->previous() !== config('app.url') . route('circles.create', [], false)
            && url()->previous() !== config('app.url') . route('circles.store', [], false)
        ) {
            return redirect()
                ->route('circles.terms');
        }

        return view('v2.circles.form')
            ->with('form', $form)
            ->with('questions', $form->questions()->get());
    }
}
