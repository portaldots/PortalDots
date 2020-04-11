<?php

namespace App\Http\Controllers\Staff\Forms\Answers;

use Auth;
use App\Http\Controllers\Controller;
use App\Eloquents\Form;
use App\Eloquents\Circle;
use App\Http\Requests\Staff\Forms\AnswerRequest;
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

    public function __invoke(Form $form, AnswerRequest $request)
    {
        // カスタムフォームの作成は許可しない
        if (isset($form->customForm)) {
            abort(404);
        }

        $circle = Circle::submitted()->findOrFail($request->circle_id);
        $answer = $this->answersService->createAnswer($form, $circle, $request);
        if ($answer) {
            if ($form->is_public) {
                // フォームが公開されている場合にのみ確認メールを送信する
                $this->answersService->sendAll($answer, Auth::user(), true);
            }
            return redirect()
                ->route('staff.forms.answers.edit', ['form' => $form, 'answer' => $answer])
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
