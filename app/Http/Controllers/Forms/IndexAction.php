<?php

namespace App\Http\Controllers\Forms;

use App\Eloquents\Circle;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eloquents\Form;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class IndexAction extends Controller
{
    public function __invoke(Request $request)
    {
        $forms = Form::public()->open()->closeOrder()->get();
        $circle = Circle::find($request->circle);
        if (empty($circle) || Gate::denies('circle.belongsTo', $circle)) {
            $circles = Auth::user()->circles()->get();
            if (count($circles) < 1) {
                return view('v2.forms.list');
            } elseif (count($circles) === 1) {
                return redirect()
                    ->route('forms.index', ['circle' => $circles[0]]);
            } elseif (count($circles) > 1) {
                return redirect()
                    ->route('circles.selector.show', ['redirect' => 'forms.index']);
            }
        }

        return view('v2.forms.list')
            ->with('forms', $forms)
            ->with('circle', $circle)
            ->with('now', now());
    }
}
