<?php

namespace App\Http\Controllers\Staff\Forms\Editor;

use App\Eloquents\Form;
use App\Services\Forms\QuestionsService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UpdateQuestionsOrderAction extends Controller
{
    private $questionsService;

    public function __construct(QuestionsService $questionsService)
    {
        $this->questionsService = $questionsService;
    }

    public function __invoke(int $form_id, Request $request)
    {
        $form = Form::withoutGlobalScope('withoutCustomForms')->findOrFail($form_id);
        $this->questionsService->updateQuestionsOrder(
            $form,
            collect($request->questions)->mapWithKeys(function ($question) {
                return [$question['id'] => $question['priority']];
            })->toArray()
        );
    }
}
