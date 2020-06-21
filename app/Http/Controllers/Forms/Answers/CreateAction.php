<?php

namespace App\Http\Controllers\Forms\Answers;

use Auth;
use Gate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eloquents\Form;
use App\Eloquents\Circle;
use App\Services\Forms\AnswersService;
use App\Services\Circles\SelectorService;

class CreateAction extends Controller
{
    /**
     * @var AnswersService
     */
    private $answersService;

    /**
     * @var SelectorService
     */
    private $selectorService;

    public function __construct(
        AnswersService $answersService,
        SelectorService $selectorService
    ) {
        $this->answersService = $answersService;
        $this->selectorService = $selectorService;
    }

    public function __invoke(Form $form)
    {
        if (! $form->is_public || isset($form->customForm)) {
            abort(404);
        }

        $this->authorize('view', [$form, $this->selectorService->getCircle()]);

        $circles = Auth::user()->circles()->approved()->get();
        if (count($circles) < 1) {
            // TODO: もうちょっとまともなエラー表示にする
            return redirect()
                ->route('home')
                ->with('topAlert.type', 'danger')
                ->with('topAlert.title', '企画に所属していないため、このページにアクセスできません');
        }

        $circle = $this->selectorService->getCircle();

        // すでに回答済だった場合
        $answers = $this->answersService->getAnswersByCircle($form, $circle);
        if ($form->max_answers === 1 && count($answers) === 1) {
            // 最大回答回数 1 かつ 現時点での回答数が 1 の場合に限り、
            // 編集画面へリダイレクトする。
            // （それ以外の場合、View にて、回答編集も可能な旨を表示する）
            return redirect()
                ->route('forms.answers.edit', ['form' => $form, 'answer' => $answers[0]]);
        }

        return view('forms.answers.form')
            ->with('circle', $circle)
            ->with('answers', $answers)
            ->with('form', $form)
            ->with('questions', $form->questions()->get());
    }
}
