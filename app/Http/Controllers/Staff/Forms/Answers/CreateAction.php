<?php

namespace App\Http\Controllers\Staff\Forms\Answers;

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
        // カスタムフォームの編集は許可しない
        if (isset($form->customForm)) {
            abort(404);
        }

        $circle = null;
        if (empty($request->circle)) {
            $circles = Circle::all();
            return view('v2.circles.selector')
                ->with('url', route('staff.forms.answers.create', ['form' => $form]))
                ->with('circles', $circles);
        } else {
            $circle = Circle::findOrFail($request->circle);
        }

        // すでに回答済だった場合
        $answers = $this->answersService->getAnswersByCircle($form, $circle);
        if ($form->max_answers === 1 && count($answers) === 1) {
            // 最大回答回数 1 かつ 現時点での回答数が 1 の場合に限り、
            // 編集画面へリダイレクトする。
            // （それ以外の場合、View にて、回答編集も可能な旨を表示する）
            return redirect()
                ->route('staff.forms.answers.edit', ['form' => $form, 'answer' => $answers[0]]);
        }

        return view('v2.staff.forms.answers.form')
            ->with('circle', $circle)
            ->with('answers', $answers)
            ->with('form', $form)
            ->with('questions', $form->questions()->get());
    }
}
