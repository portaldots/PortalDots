<?php

namespace App\Http\Controllers\Circles;

use App\Http\Controllers\Controller;
use App\Eloquents\Circle;
use App\Http\Requests\Circles\SubmitRequest;
use App\Services\Circles\CirclesService;
use App\Services\Forms\AnswerDetailsService;
use Carbon\Carbon;
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

    public function __invoke(SubmitRequest $request, Circle $circle)
    {
        $this->authorize('circle.update', $circle);

        if (!Auth::user()->isLeaderInCircle($circle)) {
            abort(403);
        }

        $formLastUpdatedAt = Carbon::createFromTimestamp($request->validated()['last_updated_timestamp']);
        if ($formLastUpdatedAt->notEqualTo($circle->updated_at)) {
            return redirect()
                ->route('circles.confirm', ['circle' => $circle])
                ->with('topAlert.type', 'danger')
                ->with('topAlert.title', 'もう一度参加登録を提出してください。')
                ->with('topAlert.body', '参加登録の内容が編集されたため、参加登録を提出できませんでした。')
                ->with('topAlert.keepVisible', true);
        }

        if (!$circle->canSubmit()) {
            return redirect()
                ->route('circles.users.index', ['circle' => $circle])
                ->with('topAlert.type', 'danger')
                ->with('topAlert.title', '参加登録に必要な人数が揃っていないか最大人数を超過しているため、参加登録の提出はまだできません。')
                ->with('topAlert.keepVisible', true);
        }

        $this->circlesService->submit($circle);

        $circle->load('users');

        $answer = $circle->getParticipationFormAnswer();
        $questions = $circle->participationType->form->questions;
        $answerDetails = !empty($answer)
            ? $this->answerDetailsService->getAnswerDetailsByAnswer($answer) : [];

        foreach ($circle->users as $user) {
            $this->circlesService->sendSubmitedEmail(
                user: $user,
                circle: $circle,
                participationForm: $circle->participationType->form,
                questions: $questions,
                answer: $answer,
                answerDetails: $answerDetails
            );
        }

        return redirect()
            ->route('circles.done', ['circle' => $circle])
            ->with('done', true);
    }
}
