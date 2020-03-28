<?php

namespace App\Http\Controllers\Circles;

use Auth;
use DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\Circles\CircleRequest;
use App\Services\Circles\CirclesService;
use App\Services\Forms\AnswersService;
use App\Eloquents\Circle;
use App\Eloquents\CustomForm;

class UpdateAction extends Controller
{
    /**
     * @var CirclesService
     */
    private $circlesService;

    /**
     * @var AnswersService
     */
    private $answersService;

    public function __construct(CirclesService $circlesService, AnswersService $answersService)
    {
        $this->circlesService = $circlesService;
        $this->answersService = $answersService;
    }

    public function __invoke(CircleRequest $request, Circle $circle)
    {
        $this->authorize('circle.update', $circle);

        DB::transaction(function () use ($request, $circle) {
            $this->circlesService->update(
                $circle,
                $request->name,
                $request->name_yomi,
                $request->group_name,
                $request->group_name_yomi
            );

            $custom_form_answer = $circle->getCustomFormAnswer();

            if (empty($custom_form_answer)) {
                $this->answersService->createAnswer(
                    CustomForm::getFormByType('circle'),
                    $circle,
                    $request
                );
            } else {
                $this->answersService->updateAnswer(
                    CustomForm::getFormByType('circle'),
                    $custom_form_answer,
                    $request
                );
            }
        });

        return redirect()
            ->route('circles.users.index', ['circle' => $circle]);
    }
}
