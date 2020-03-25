<?php

namespace App\Http\Controllers\Circles\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Eloquents\Circle;

class IndexAction extends Controller
{
    public function __invoke(Circle $circle, Request $request)
    {
        $this->authorize('circle.belongsTo', $circle);

        $circle->load('users');

        $invitation_url = ($request->isSecure() ? 'https:' : 'http:')
            . route('circles.users.invite', ['circle' => $circle, 'token' => $circle->invitation_token]);

        return view('v2.circles.users.index')
            ->with('circle', $circle)
            ->with('invitation_url', str_replace('"', '', \json_encode($invitation_url, JSON_UNESCAPED_SLASHES)))
            ->with('share_json', \json_encode([
                'url' => $invitation_url,
            ], JSON_UNESCAPED_SLASHES));
    }
}
