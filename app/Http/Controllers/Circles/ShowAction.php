<?php

namespace App\Http\Controllers\Circles;

use App\Eloquents\Circle;
use App\Http\Controllers\Controller;
use App\Services\Forms\AnswerDetailsService;
use Carbon\CarbonImmutable;

class ShowAction extends Controller
{
    /**
     * @var AnswerDetailsService
     */
    private $answerDetailsService;

    public function __construct(
        AnswerDetailsService $answerDetailsService
    ) {
        $this->answerDetailsService = $answerDetailsService;
    }

    public function __invoke(Circle $circle)
    {
        $this->authorize('circle.belongsTo', $circle);

        $reauthorized_at = new CarbonImmutable(session()->get('user_reauthorized_at'));

        if (
            !$circle->hasSubmitted()
            || (session()->has('user_reauthorized_at') && $reauthorized_at->addHours(2)->gte(now()))
        ) {
            $circle->load('users', 'places', 'participationType', 'participationType.form');

            $answer = $circle->getParticipationFormAnswer();

            return view('circles.show')
                ->with('circle', $circle)
                ->with('form', isset($circle->participationType) ? $circle->participationType->form : null)
                ->with('questions', isset($circle->participationType)
                    ? $circle->participationType->form->questions
                    : null)
                ->with('answer', $answer)
                ->with('answer_details', !empty($answer)
                    ? $this->answerDetailsService->getAnswerDetailsByAnswer($answer) : []);
        }
        return redirect()
            ->route('circles.auth', ['circle' => $circle]);
    }
}
