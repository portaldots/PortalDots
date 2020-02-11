<?php

namespace App\Http\Controllers\Forms\Answers;

use App\Http\Controllers\Controller;
use App\Eloquents\Form;
use App\Eloquents\Circle;
use App\Http\Requests\Forms\AnswerRequest;
use App\Services\Forms\AnswersService;

class StoreAction extends Controller
{
    private $answersService;

    public function __construct(AnswersService $answersService)
    {
        $this->answersService = $answersService;
    }

    public function __invoke(Form $form, AnswerRequest $request)
    {
        $circle = Circle::findOrFail($request->circle_id);
        $this->answersService->createAnswer($form, $circle, $request->answers);
    }
}
