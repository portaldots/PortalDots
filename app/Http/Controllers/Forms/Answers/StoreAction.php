<?php

namespace App\Http\Controllers\Forms\Answers;

use Auth;
use App\Http\Controllers\Controller;
use App\Eloquents\Form;
use App\Eloquents\Circle;
use App\Http\Requests\Forms\StoreAnswerRequest;
use App\Services\Forms\AnswersService;

class StoreAction extends Controller
{
    /**
     * @var AnswersService
     */
    private $answersService;

    public function __construct(AnswersService $answersService)
    {
        $this->answersService = $answersService;
    }

    public function __invoke(Form $form, StoreAnswerRequest $request)
    {
        if (isset($form->customForm)) {
            abort(404);
        }

        $this->authorize('view', [$form, Circle::findOrFail($request->circle_id)]);

        // ユーザーが企画に所属しているかどうかの検証は
        // StoreAnswerRequest で行っている
        $circle = Circle::approved()->findOrFail($request->circle_id);
        $answer = $this->answersService->createAnswer($form, $circle, $request);
        if ($answer) {
            $this->answersService->sendAll($answer, Auth::user());
            return redirect()
                ->route('forms.answers.edit', ['form' => $form, 'answer' => $answer])
                ->with('topAlert.title', '回答を作成しました — 回答ID : ' . $answer->id)
                ->with('topAlert.body', '以下のフォームより、回答を修正することもできます');
        }

        return back()
                ->with('topAlert.type', 'danger')
                ->with('topAlert.title', '更新に失敗しました')
                ->with('topAlert.body', '恐れ入りますが、もう一度お試しください')
                ->withInput();
    }
}
