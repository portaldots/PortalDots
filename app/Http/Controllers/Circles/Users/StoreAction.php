<?php

namespace App\Http\Controllers\Circles\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Eloquents\Circle;
use App\Services\Circles\CirclesService;
use Illuminate\Support\Facades\Auth;

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

        $participationForm = $circle->participationType->form;

        $canJoin = isset($participationForm)
            && $participationForm->is_public
            && $participationForm->isOpen()
            && !$circle->hasSubmitted();

        // FIXME: 参加可能人数の上限に達している場合もエラーにしたい
        if (!$canJoin) {
            abort(404);
        }

        if ($circle->users->contains(Auth::user())) {
            return redirect()
                ->route('circles.show', ['circle' => $circle])
                ->with('topAlert.title', 'あなたは既にメンバーです');
        }

        activity()->disableLogging();
        $this->circlesService->addMember($circle, Auth::user());
        activity()->enableLogging();

        return redirect()
            ->route('circles.show', ['circle' => $circle])
            ->with('topAlert.title', "「{$circle->name}」の学園祭係(副責任者)になりました");
    }
}
