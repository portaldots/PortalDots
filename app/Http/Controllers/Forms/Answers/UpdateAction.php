<?php

namespace App\Http\Controllers\Forms\Answers;

use Auth;
use App\Http\Controllers\Controller;
use App\Eloquents\Form;
use App\Eloquents\Circle;
use App\Eloquents\Answer;
use App\Http\Requests\Forms\UpdateAnswerRequest;
use App\Services\Forms\AnswersService;

class UpdateAction extends Controller
{
    private $answersService;

    public function __construct(AnswersService $answersService)
    {
        $this->answersService = $answersService;
    }

    public function __invoke(Form $form, Answer $answer, UpdateAnswerRequest $request)
    {
        $this->answersService->updateAnswer($form, $answer, $request);
        $this->answersService->sendAll($answer, Auth::user());
        return back()
            ->with('topAlert.title', '回答を更新しました');

        // return back()
        //         ->with('topAlert.type', 'danger')
        //         ->with('topAlert.title', '更新に失敗しました')
        //         ->with('topAlert.body', '恐れ入りますが、もう一度お試しください')
        //         ->withInput();
    }
}
