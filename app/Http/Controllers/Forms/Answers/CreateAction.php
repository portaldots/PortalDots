<?php

namespace App\Http\Controllers\Forms\Answers;

use Auth;
use Gate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eloquents\Form;
use App\Eloquents\Circle;
use App\Services\Forms\AnswersService;

class CreateAction extends Controller
{
    private $answersService;

    public function __construct(AnswersService $answersService)
    {
        $this->answersService = $answersService;
    }

    public function __invoke(Form $form, Request $request)
    {
        if (! $form->is_public) {
            abort(404);
            return;
        }

        $circle = null;
        if (empty($request->circle)) {
            $circles = Auth::user()->circles;
            if (count($circles) < 1) {
                // TODO: もうちょっとまともなエラー表示にする
                return redirect()
                    ->route('home')
                    ->with('topAlert.type', 'danger')
                    ->with('topAlert.title', '企画に所属していないため、このページにアクセスできません');
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

        // すでに回答済だった場合
        $answers = $this->answersService->getAnswersByCircle($form, $circle);
        if ($form->max_answers === 1 && count($answers) === 1) {
            // 最大回答回数 1 かつ 現時点での回答数が 1 の場合に限り、
            // 編集画面へリダイレクトする。
            // （それ以外の場合、View にて、回答編集も可能な旨を表示する）
            return redirect()
                ->route('forms.answers.edit', ['form' => $form, 'answer' => $answers[0]]);
        }

        return view('v2.forms.answers.form')
            ->with('circle', $circle)
            ->with('answers', $answers)
            ->with('form', $form)
            ->with('questions', $form->questions()->get());
    }
}
