<?php

namespace App\Http\Controllers\Circles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Circles\CircleRequest;
use App\Services\Circles\CirclesService;
use App\Services\Forms\AnswersService;
use App\Eloquents\CustomForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StoreAction extends Controller
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

    public function __invoke(CircleRequest $request)
    {
        $this->authorize('circle.create');

        activity()->disableLogging();

        $result = DB::transaction(function () use ($request) {
            $circle = $this->circlesService->create(
                Auth::user(),
                $request->name,
                $request->name_yomi,
                $request->group_name,
                $request->group_name_yomi
            );

            $this->answersService->createAnswer(
                CustomForm::getFormByType('circle'),
                $circle,
                $request
            );

            return redirect()
                ->route('circles.users.index', ['circle' => $circle]);
        });

        activity()->enableLogging();

        return $result;
    }
}
