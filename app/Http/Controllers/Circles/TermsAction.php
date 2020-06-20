<?php

namespace App\Http\Controllers\Circles;

use App\Eloquents\CustomForm;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TermsAction extends Controller
{
    public function __invoke()
    {
        $this->authorize('circle.create');

        $form = CustomForm::getFormByType('circle');

        if (empty($form->description)) {
            return redirect()
                ->route('circles.create');
        }

        session()->put('read_terms', true);

        return view('circles.terms')
            ->with('description', $form->description);
    }
}
