<?php

namespace App\Http\Controllers\Circles\Users;

use App\Http\Controllers\Controller;
use App\Eloquents\Circle;
use App\Eloquents\CustomForm;
use Illuminate\Support\Facades\Auth;

class InviteAction extends Controller
{
    public function __invoke(Circle $circle, string $token)
    {
        if ($circle->invitation_token !== $token) {
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
            $redirect_to = 'circles.show';
            if (Auth::user()->isLeaderInCircle($circle)) {
                $redirect_to = 'circles.users.index';
            }
            return redirect()
                ->route($redirect_to, ['circle' => $circle])
                ->with('topAlert.title', 'あなたは既にメンバーです');
        }

        return view('circles.users.invite')
            ->with('circle', $circle);
    }
}
