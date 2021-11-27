<?php

namespace App\Http\Controllers\Circles;

use App\Http\Controllers\Controller;
use App\Eloquents\Circle;
use App\Eloquents\CustomForm;
use App\Services\Circles\CirclesService;
use App\Services\Forms\AnswerDetailsService;
use Illuminate\Support\Facades\Auth;

class SubmitAction extends Controller
{
    /**
     * @var CirclesService
     */
    private $circlesService;

    /**
     * @var AnswerDetailsService
     */
    private $answerDetailsService;

    public function __construct(CirclesService $circlesService, AnswerDetailsService $answerDetailsService)
    {
        $this->circlesService = $circlesService;
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
                ->with('topAlert.title', '参加登録に必要な人数が揃っていないため、参加登録の提出はまだできません');
        }

        $this->circlesService->submit($circle);

        $circle->load('users');

        $form = CustomForm::getFormByType('circle');
        $answer = !empty($form) ? $circle->getCustomFormAnswer() : null;
        $questions = !empty($form) ? $form->questions()->get() : null;
        $answerDetails = !empty($answer)
            ? $this->answerDetailsService->getAnswerDetailsByAnswer($answer) : [];

        foreach ($circle->users as $user) {
            $this->circlesService->sendSubmitedEmail(
                $user,
                $circle,
                $form,
                $questions,
                $answer,
                $answerDetails
            );
        }

        return redirect()
            ->route('home')
            ->with('topAlert.title', '企画参加登録を提出しました！');
    }
}
