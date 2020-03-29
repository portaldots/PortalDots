<?php

namespace App\Http\Controllers\Circles\Users;

use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Eloquents\Circle;
use App\Eloquents\CustomForm;
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

        $custom_form = CustomForm::getFormByType('circle');

        $can_join = isset($custom_form)
            && $custom_form->is_public
            && $custom_form->isOpen()
            && !$circle->hasSubmitted();

        if (!$can_join) {
            abort(404);
        }

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
