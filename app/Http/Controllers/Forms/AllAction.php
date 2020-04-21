<?php

namespace App\Http\Controllers\Forms;

use App\Eloquents\Circle;
use App\Eloquents\Form;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AllAction extends Controller
{
    public function __invoke(Request $request)
    {
        $forms = Form::public()->withoutCustomForms()->closeOrder()->paginate(10);
        $circle = Circle::approved()->find($request->circle);
        if (empty($circle) || Gate::denies('circle.belongsTo', $circle)) {
            $circles = Auth::user()->circles()->approved()->get();
            if (count($circles) < 1) {
                return view('v2.forms.list');
            } elseif (count($circles) === 1) {
                return redirect()
                    ->route('forms.all', ['circle' => $circles[0]]);
            } elseif (count($circles) > 1) {
                return redirect()
                    ->route('circles.selector.show', ['redirect' => 'forms.all']);
            }
        }

        if ($forms->currentPage() > $forms->lastPage()) {
            return redirect($forms->url($forms->lastPage()));
        }

        return view('v2.forms.list')
            ->with('forms', $forms)
            ->with('circle', $circle)
            ->with('now', now());
    }
}
