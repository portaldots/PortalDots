<?php

namespace App\Http\Controllers\Circles;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Eloquents\Circle;

class DeleteAction extends Controller
{
    public function __invoke(Circle $circle)
    {
        $this->authorize('circle.update', $circle);

        $user = $circle->users()->wherePivot('user_id', Auth::id())->first();

        if (empty($user) || !$user->pivot->is_leader) {
            // リーダー以外は参加登録の削除はできない
            abort(403);
        }

        return view('circles.delete')
            ->with('circle', $circle);
    }
}
