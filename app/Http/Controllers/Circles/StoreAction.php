<?php

namespace App\Http\Controllers\Circles;

use Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\Circles\CircleRequest;
use App\Services\Circles\CirclesService;

class StoreAction extends Controller
{
    /**
     * @var CirclesService
     */
    private $circlesService;

    public function __construct(CirclesService $circlesService)
    {
        $this->circlesService = $circlesService;
    }

    public function __invoke(CircleRequest $request)
    {
        $circle = $this->circlesService->create(
            Auth::user(),
            $request->name,
            $request->name_yomi,
            $request->group_name,
            $request->group_name_yomi
        );

        return redirect()
            ->route('circles.users.index', ['circle' => $circle]);
    }
}
