<?php

namespace App\Http\Controllers\Staff\Forms\Editor;

use App\Eloquents\Form;
use App\Services\Forms\QuestionsService;
use App\Http\Requests\Staff\Forms\Editor\UpdateQuestionsOrderRequest;
use App\Http\Controllers\Controller;

class UpdateQuestionsOrderAction extends Controller
{
    private $questionsService;

    public function __construct(QuestionsService $questionsService)
    {
        $this->questionsService = $questionsService;
    }

    public function __invoke(Form $form, UpdateQuestionsOrderRequest $request)
    {
        $this->questionsService->updateQuestionsOrder(
            $form,
            collect($request->questions)->mapWithKeys(function ($question) {
                return [$question['id'] => $question['priority']];
            })->toArray()
        );
    }
}
