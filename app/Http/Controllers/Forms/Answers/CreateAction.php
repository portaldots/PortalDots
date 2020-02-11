<?php

namespace App\Http\Controllers\Forms\Answers;

use Auth;
use Gate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eloquents\Form;
use App\Eloquents\Circle;

class CreateAction extends Controller
{
    public function __invoke(Form $form, Request $request)
    {
        $circle = null;
        if (empty($request->circle)) {
            $circles = Auth::user()->circles;
            if (count($circles) < 1) {
                // TODO: もうちょっとまともなエラー表示にする
                abort(403, '団体に所属していないため、アクセスできません');
                return;
            } elseif (count($circles) === 1) {
                return redirect()
                    ->route('forms.answers.create', ['form' => $form,'circle' => $circles[0]]);
            } else {
                return view('v2.circles.selector')
                    ->with('url', route('forms.answers.create', ['form' => $form]))
                    ->with('circles', $circles);
            }
        } else {
            $circle = Circle::findOrFail($request->circle);
            if (Gate::denies('circle.belongsTo', $circle)) {
                // TODO: もうちょっとまともなエラー表示にする
                abort(403);
                return;
            }
        }

        $questions = $form->questions()->get();
        return view('v2.forms.answers.form')
            ->with('circle', $circle)
            ->with('form', $form)
            ->with('questions', $questions);
    }
}
