<?php

namespace App\Http\Controllers\Forms\Answers;

use Auth;
use App\Http\Controllers\Concerns\RespondsToStaleAnswer;
use App\Http\Controllers\Controller;
use App\Eloquents\Form;
use App\Eloquents\Circle;
use App\Eloquents\Answer;
use App\Exceptions\Forms\StaleAnswerException;
use App\Http\Requests\Forms\UpdateAnswerRequest;
use App\Services\Forms\AnswersService;

class UpdateAction extends Controller
{
    use RespondsToStaleAnswer;

    private $answersService;

    public function __construct(AnswersService $answersService)
    {
        $this->answersService = $answersService;
    }

    public function __invoke(Form $form, Answer $answer, UpdateAnswerRequest $request)
    {
        if (isset($form->participationType)) {
            abort(404);
        }

        $lockVersion = $request->has('lock_version') ? (int)$request->input('lock_version') : null;

        try {
            $this->answersService->updateAnswer($form, $answer, $request, Auth::user(), false, $lockVersion);
        } catch (StaleAnswerException $e) {
            return $this->staleAnswerResponse($request);
        }

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
