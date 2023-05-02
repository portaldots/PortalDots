<?php

namespace App\Http\Controllers\Circles\Users;

use App\Http\Controllers\Controller;
use App\Eloquents\Circle;
use Illuminate\Support\Facades\Auth;

class InviteAction extends Controller
{
    public function __invoke(Circle $circle, string $token)
    {
        if ($circle->invitation_token !== $token) {
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
