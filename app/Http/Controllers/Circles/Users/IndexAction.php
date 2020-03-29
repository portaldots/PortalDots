<?php

namespace App\Http\Controllers\Circles\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Eloquents\Circle;

class IndexAction extends Controller
{
    public function __invoke(Circle $circle, Request $request)
    {
        $this->authorize('circle.update', $circle);

        $circle->load('users');

        $invitation_url_for_qr = route('circles.users.invite', [
            'circle' => $circle,
            'token' => $circle->invitation_token
        ]);

        $invitation_url = ($request->isSecure() ? 'https:' : 'http:')
            . $invitation_url_for_qr;

        return view('v2.circles.users.index')
            ->with('circle', $circle)
            ->with('invitation_url', str_replace('"', '', \json_encode($invitation_url, JSON_UNESCAPED_SLASHES)))
            ->with('invitation_url_for_qr', $invitation_url_for_qr)
            ->with('share_json', \json_encode([
                'url' => $invitation_url,
            ], JSON_UNESCAPED_SLASHES));
    }
}
