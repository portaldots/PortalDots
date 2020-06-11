<?php

namespace App\Http\Controllers\Circles;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Eloquents\Circle;

class DestroyAction extends Controller
{
    public function __invoke(Circle $circle)
    {
        $this->authorize('circle.update', $circle);

        $user = $circle->users()->wherePivot('user_id', Auth::id())->first();

        if (empty($user) || !$user->pivot->is_leader) {
            // リーダー以外は参加登録の削除はできない
            abort(403);
        }

        return DB::transaction(function () use ($circle) {
            $circle->users()->detach();
            $circle->answers()->delete();
            $circle->delete();

            return redirect()
                ->route('home')
                ->with('topAlert.title', '企画参加登録を削除しました');
        });
    }
}
