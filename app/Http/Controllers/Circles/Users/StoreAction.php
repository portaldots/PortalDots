<?php

namespace App\Http\Controllers\Circles\Users;

use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Eloquents\Circle;
use App\Services\Circles\CirclesService;

class StoreAction extends Controller
{
    private $circlesService;

    public function __construct(CirclesService $circlesService)
    {
        $this->circlesService = $circlesService;
    }

    public function __invoke(Circle $circle, Request $request)
    {
        if ($circle->invitation_token !== $request->invitation_token) {
            abort(404);
        }

        $this->authorize('circle.update', $circle);

        if ($circle->users->contains(Auth::user())) {
            return redirect()
                ->route('circles.users.index', ['circle' => $circle])
                ->with('topAlert.title', 'あなたは既にメンバーです');
        }

        $result = $this->circlesService->addMember($circle, Auth::user());

        return redirect()
                ->route('circles.users.index', ['circle' => $circle])
                ->with('topAlert.title', "「{$circle->name}」の学園祭係(副責任者)になりました");
    }
}
