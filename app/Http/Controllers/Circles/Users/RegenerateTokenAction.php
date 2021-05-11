<?php

namespace App\Http\Controllers\Circles\Users;

use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Circles\CirclesService;
use App\Eloquents\Circle;

class RegenerateTokenAction extends Controller
{
    /**
     * @var CirclesService
     */
    private $circlesService;

    public function __construct(CirclesService $circlesService)
    {
        $this->circlesService = $circlesService;
    }

    public function __invoke(Circle $circle)
    {
        $this->authorize('circle.update', $circle);

        if (!Auth::user()->isLeaderInCircle($circle)) {
            abort(403);
        }

        activity()->disableLogging();
        $this->circlesService->regenerateInvitationToken($circle);
        activity()->enableLogging();

        return redirect()
            ->route('circles.users.index', ['circle' => $circle])
            ->with('topAlert.title', '招待URLを新しくつくりなおしました');
    }
}
