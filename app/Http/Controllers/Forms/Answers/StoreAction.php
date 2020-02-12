<?php

namespace App\Http\Controllers\Forms\Answers;

use App\Http\Controllers\Controller;
use App\Eloquents\Form;
use App\Eloquents\Circle;
use App\Http\Requests\Forms\StoreAnswerRequest;
use App\Services\Forms\AnswersService;

class StoreAction extends Controller
{
    private $answersService;

    public function __construct(AnswersService $answersService)
    {
        $this->answersService = $answersService;
    }

    public function __invoke(Form $form, StoreAnswerRequest $request)
    {
        $circle = Circle::findOrFail($request->circle_id);
        $answer = $this->answersService->createAnswer($form, $circle, $request);
        if ($answer) {
            return redirect()
                ->route('forms.answers.edit', ['form' => $form, 'answer' => $answer])
                ->with('topAlert.title', '回答を作成しました')
                ->with('topAlert.body', '以下のフォームより、回答を修正することもできます');
        }

        return back()
                ->with('topAlert.type', 'danger')
                ->with('topAlert.title', '更新に失敗しました')
                ->with('topAlert.body', '恐れ入りますが、もう一度お試しください')
                ->withInput();
    }
}
