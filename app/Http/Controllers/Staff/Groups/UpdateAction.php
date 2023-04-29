<?php

namespace App\Http\Controllers\Staff\Groups;

use App\Http\Controllers\Controller;
use App\Eloquents\Group;
use App\Eloquents\User;
use App\Http\Requests\Staff\Groups\GroupRequest;
use Illuminate\Support\Facades\DB;

class UpdateAction extends Controller
{
    /**
     * @var User
     */
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function __invoke(GroupRequest $request, Group $group)
    {
        return DB::transaction(function () use ($request, $group) {
            $validated = $request->validated();

            $member_ids = str_replace(["\r\n", "\r", "\n"], "\n", $validated['members']);
            $member_ids = explode("\n", $member_ids);
            $member_ids = array_unique(array_filter($member_ids, "strlen"));

            $owner = $this->user->firstByStudentId($validated['owner']);
            if (!empty($owner)) {
                $member_ids = array_diff($member_ids, [$owner->student_id]);
            }

            $members = $this->user->getByStudentIdIn($member_ids);

            $group->update([
                'name' => $validated['name'],
                'name_yomi' => $validated['name_yomi'],
                'notes' => $validated['notes'],
            ]);
            $group->users()->detach();

            if (!empty($owner)) {
                $owner->groups()->attach($group->id, ['role' => 'owner']);
            }
            foreach ($members as $member) {
                $member->groups()->attach($group->id, ['role' => 'member']);
            }

            return redirect()
                ->back()
                ->with('topAlert.title', '団体を更新しました');
        });
    }
}
