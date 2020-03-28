<?php

namespace App\Http\Controllers\Circles\Users;

use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Circles\CirclesService;
use App\Eloquents\Circle;
use App\Eloquents\User;

class DestroyAction extends Controller
{
    private $circlesService;

    public function __construct(CirclesService $circlesService)
    {
        $this->circlesService = $circlesService;
    }

    public function __invoke(Circle $circle, User $user)
    {
        $this->authorize('circle.update', $circle);

        if ($user->circles()->withoutGlobalScope('approved')->findOrFail($circle->id)->pivot->is_leader) {
            return redirect()
                ->route('circles.users.index', ['circle' => $circle])
                ->with('topAlert.type', 'danger')
                ->with('topAlert.title', '責任者を削除することはできません');
        }

        $this->circlesService->removeMember($circle, $user);

        $message = 'メンバーを削除しました';

        if ($user->id === Auth::id()) {
            return redirect()
                ->route('home')
                ->with('topAlert.title', $message);
        }

        return redirect()
                ->route('circles.users.index', ['circle' => $circle])
                ->with('topAlert.title', $message);
    }
}
