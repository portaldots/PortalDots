<?php

namespace App\Http\Controllers\Forms;

use App\Eloquents\Circle;
use App\Eloquents\Form;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ClosedAction extends Controller
{
    public function __invoke(Request $request)
    {
        $forms = Form::public()->closed()->closeOrder()->paginate(10);
        $circle = Circle::find($request->circle);
        if (empty($circle) || Gate::denies('circle.belongsTo', $circle)) {
            $circles = Auth::user()->circles()->get();
            if (count($circles) < 1) {
                return view('v2.forms.list');
            } elseif (count($circles) === 1) {
                return redirect()
                    ->route('forms.closed', ['circle' => $circles[0]]);
            } elseif (count($circles) > 1) {
                return redirect()
                    ->route('circles.selector.show', ['redirect' => 'forms.closed']);
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
