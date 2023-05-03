<?php

namespace App\Http\Controllers\Circles;

use App\Http\Controllers\Controller;
use App\Eloquents\Circle;
use App\Services\Forms\AnswerDetailsService;
use Illuminate\Support\Facades\Auth;

class ConfirmAction extends Controller
{
    /**
     * @var AnswerDetailsService
     */
    private $answerDetailsService;

    public function __construct(AnswerDetailsService $answerDetailsService)
    {
        $this->answerDetailsService = $answerDetailsService;
    }

    public function __invoke(Circle $circle)
    {
        $this->authorize('circle.update', $circle);

        if (!Auth::user()->isLeaderInCircle($circle)) {
            abort(403);
        }

        if (!$circle->canSubmit()) {
            return redirect()
                ->route('circles.users.index', ['circle' => $circle])
                ->with('topAlert.type', 'danger')
                ->with('topAlert.title', '参加登録に必要な人数が揃っていないか最大人数を超過しているため、参加登録の提出はまだできません。');
        }

        $circle->load('users');

        $answer = $circle->getParticipationFormAnswer();

        return view('circles.confirm')
            ->with('circle', $circle)
            ->with('form', $circle->participationType->form)
            ->with('questions', $circle->participationType->form->questions)
            ->with('answer', $answer)
            ->with('answer_details', !empty($answer)
                ? $this->answerDetailsService->getAnswerDetailsByAnswer($answer) : []);
    }
}
