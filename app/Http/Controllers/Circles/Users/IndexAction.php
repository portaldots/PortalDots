<?php

namespace App\Http\Controllers\Circles\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Eloquents\Circle;
use Illuminate\Support\Facades\Gate;

class IndexAction extends Controller
{
    public function __invoke(Circle $circle, Request $request)
    {
        if (Gate::denies('circle.update', $circle)) {
            $this->authorize('circle.belongsTo', $circle);

            return redirect()
                ->route('circles.read', ['circle' => $circle]);
        }

        $circle->load('users');

        // 開発環境で http: が表示されないことがあるが、開発環境以外では
        // 正常に表示されるので問題がない
        $invitation_url = route('circles.users.invite', [
            'circle' => $circle,
            'token' => $circle->invitation_token
        ]);

        return view('v2.circles.users.index')
            ->with('circle', $circle)
            ->with('invitation_url', str_replace('"', '', \json_encode($invitation_url, JSON_UNESCAPED_SLASHES)))
            ->with('share_json', \json_encode([
                'url' => $invitation_url,
            ], JSON_UNESCAPED_SLASHES));
    }
}
