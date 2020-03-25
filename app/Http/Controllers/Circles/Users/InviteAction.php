<?php

namespace App\Http\Controllers\Circles\Users;

use Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Eloquents\Circle;

class InviteAction extends Controller
{
    public function __invoke(Circle $circle, string $token)
    {
        if ($circle->invitation_token !== $token) {
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
