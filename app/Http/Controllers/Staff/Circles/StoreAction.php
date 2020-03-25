<?php

namespace App\Http\Controllers\Staff\Circles;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Eloquents\User;
use App\Eloquents\Circle;
use App\Http\Requests\Circles\StaffCircleRequest;

class StoreAction extends Controller
{
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function __invoke(StaffCircleRequest $request)
    {
        $member_ids = str_replace(["\r\n", "\r", "\n"], "\n", $request->members);
        $member_ids = explode("\n", $member_ids);
        $member_ids = array_unique(array_filter($member_ids, "strlen"));

        $leader = $this->user->firstByStudentId($request->leader);
        if (!empty($leader)) {
            $member_ids = array_diff($member_ids, [$leader->student_id]);
        }

        $members = $this->user->getByStudentIdIn($member_ids);

        // 保存処理
        $circle = Circle::create([
            'name'  => $request->name,
            'name_yomi'  => $request->name_yomi,
            'group_name'  => $request->group_name,
            'group_name_yomi'  => $request->group_name_yomi,
            'notes' => $request->notes
        ]);
        $circle->users()->detach();

        if (!empty($leader)) {
            $leader->circles()->attach($circle->id, ['is_leader' => true]);
        }
        foreach ($members as $member) {
            $member->circles()->attach($circle->id, ['is_leader' => false]);
        }

        return redirect()
            ->route('staff.circles.create')
            ->with('toast', '企画情報を作成しました');
    }
}
