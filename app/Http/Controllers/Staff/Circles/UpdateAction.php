<?php

namespace App\Http\Controllers\Staff\Circles;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eloquents\Circle;
use App\Eloquents\User;
use App\Http\Requests\Staff\Circles\CircleRequest;
use Illuminate\Support\Facades\Auth;

class UpdateAction extends Controller
{
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function __invoke(Circle $circle, CircleRequest $request)
    {
        if (!$circle->hasSubmitted()) {
            // 参加登録が未提出の企画の情報は閲覧・編集できない
            abort(404);
        }

        $member_ids = str_replace(["\r\n", "\r", "\n"], "\n", $request->members);
        $member_ids = explode("\n", $member_ids);
        $member_ids = array_unique(array_filter($member_ids, "strlen"));

        $leader = $this->user->firstByStudentId($request->leader);
        if (!empty($leader)) {
            $member_ids = array_diff($member_ids, [$leader->student_id]);
        }
        $members = $this->user->getByStudentIdIn($member_ids);

        $status = $circle->status;
        $status_set_at = $circle->status_set_at;
        $status_set_by = $circle->status_set_by;

        if ($request->status === Circle::STATUS_PENDING) {
            $status = null;
            $status_set_at = null;
            $status_set_by = null;
        } elseif (
            in_array($request->status, [Circle::STATUS_APPROVED, Circle::STATUS_REJECTED], true) &&
            $request->status !== $circle->status
        ) {
            $status = $request->status;
            $status_set_at = now();
            $status_set_by = Auth::id();
        }

        // 保存処理
        $circle->update([
            'name'  => $request->name,
            'name_yomi'  => $request->name_yomi,
            'group_name'  => $request->group_name,
            'group_name_yomi'  => $request->group_name_yomi,
            'status' => $status,
            'status_reason' => $request->status_reason,
            'status_set_at' => $status_set_at,
            'status_set_by' => $status_set_by,
            'notes' => $request->notes
        ]);
        $circle->users()->detach();

        if (!empty($leader)) {
            $leader->circles()->attach($circle->id, ['is_leader' => true]);
        }
        foreach ($members as $member) {
            $member->circles()->attach($circle->id, ['is_leader' => false]);
        }
        $circle->save();
        return redirect()
            ->route('staff.circles.edit', $circle)
            ->with('toast', '企画情報を更新しました');
    }
}
