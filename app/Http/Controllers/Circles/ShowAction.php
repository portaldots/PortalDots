<?php

namespace App\Http\Controllers\Circles;

use App\Eloquents\Circle;
use App\Eloquents\CustomForm;
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
            $circle->load('users', 'places');

            $form = CustomForm::getFormByType('circle');
            $answer = $circle->getCustomFormAnswer();

            return view('circles.show')
                ->with('circle', $circle)
                ->with('form', $form)
                ->with('questions', $form->questions()->get())
                ->with('answer', $answer)
                ->with('answer_details', $this->answerDetailsService->getAnswerDetailsByAnswer($answer));
        }
        return redirect()
            ->route('circles.auth', ['circle' => $circle]);
    }
}
