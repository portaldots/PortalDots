<?php

namespace App\Http\Controllers\Circles\Users;

use App\Http\Controllers\Controller;
use App\Services\Circles\CirclesService;
use App\Eloquents\Circle;
use App\Eloquents\User;
use Illuminate\Support\Facades\Auth;

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

        if ($user->circles()->findOrFail($circle->id)->pivot->is_leader) {
            return redirect()
                ->route('circles.users.index', ['circle' => $circle])
                ->with('topAlert.type', 'danger')
                ->with('topAlert.title', '責任者を削除することはできません');
        }

        if (!Auth::user()->isLeaderInCircle($circle) && $user->id !== Auth::id()) {
            return redirect()
                ->route('circles.show', ['circle' => $circle])
                ->with('topAlert.type', 'danger')
                ->with('topAlert.title', '他のメンバーを削除することはできません');
        }

        activity()->disableLogging();
        $this->circlesService->removeMember($circle, $user);
        activity()->enableLogging();

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
