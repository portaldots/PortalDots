<?php

namespace App\Http\Controllers\Circles\Users;

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

        $this->circlesService->regenerateInvitationToken($circle);

        return redirect()
            ->route('circles.users.index', ['circle' => $circle])
            ->with('topAlert.title', '招待URLを新しくつくりなおしました');
    }
}
