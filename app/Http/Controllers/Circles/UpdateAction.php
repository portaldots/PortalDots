<?php

namespace App\Http\Controllers\Circles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Circles\CircleRequest;
use App\Services\Circles\CirclesService;
use App\Services\Forms\AnswersService;
use App\Eloquents\Circle;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        if (!Auth::user()->isLeaderInCircle($circle)) {
            abort(403);
        }

        activity()->disableLogging();

        DB::transaction(function () use ($request, $circle) {
            $this->circlesService->update(
                circle: $circle,
                name: $request->name,
                name_yomi: $request->name_yomi,
                group_name: $request->group_name,
                group_name_yomi: $request->group_name_yomi
            );

            $participationFormAnswer = $circle->getParticipationFormAnswer();

            $circle->touch();

            if (empty($participationFormAnswer)) {
                $this->answersService->createAnswer(
                    form: $circle->participationType->form,
                    circle: $circle,
                    request: $request
                );
            } else {
                $this->answersService->updateAnswer(
                    form: $circle->participationType->form,
                    answer: $participationFormAnswer,
                    request: $request
                );
            }
        });

        activity()->enableLogging();

        return redirect()
            ->route('circles.users.index', ['circle' => $circle]);
    }
}
