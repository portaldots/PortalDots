<?php

namespace App\Http\Controllers\Circles\Users;

use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Eloquents\Circle;
use App\Eloquents\CustomForm;

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
            return redirect()
                ->route('circles.users.index', ['circle' => $circle])
                ->with('topAlert.title', 'あなたは既にメンバーです');
        }

        return view('v2.circles.users.invite')
            ->with('circle', $circle);
    }
}
