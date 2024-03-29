<?php

namespace App\Http\Controllers\Staff\Circles;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Eloquents\User;
use App\Eloquents\Circle;
use App\Http\Requests\Staff\Circles\CreateCircleRequest;
use App\Services\Circles\CirclesService;
use App\Services\Tags\Exceptions\DenyCreateTagsException;
use Illuminate\Support\Facades\DB;

class StoreAction extends Controller
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var CirclesService
     */
    private $circlesService;

    public function __construct(User $user, CirclesService $circlesService)
    {
        $this->user = $user;
        $this->circlesService = $circlesService;
    }

    public function __invoke(CreateCircleRequest $request)
    {
        DB::beginTransaction();

        $member_ids = str_replace(["\r\n", "\r", "\n"], "\n", $request->members);
        $member_ids = explode("\n", $member_ids);
        $member_ids = array_unique(array_filter($member_ids, "strlen"));

        $leader = $this->user->firstByStudentId($request->leader);
        if (!empty($leader)) {
            $member_ids = array_diff($member_ids, [$leader->student_id]);
        }

        $members = $this->user->getByStudentIdIn($member_ids);

        $status = null;
        $status_set_at = null;
        $status_set_by = null;

        if (in_array($request->status, [Circle::STATUS_APPROVED, Circle::STATUS_REJECTED], true)) {
            $status = $request->status;
            $status_set_at = now();
            $status_set_by = Auth::id();
        }

        // 保存処理
        $circle = Circle::create([
            'participation_type_id' => $request->participation_type_id,
            'name'  => $request->name,
            'name_yomi'  => $request->name_yomi,
            'group_name'  => $request->group_name,
            'group_name_yomi'  => $request->group_name_yomi,
            // スタッフモードでの企画作成は、参加登録提出済とみなす
            'submitted_at' => now(),
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

        // 場所の保存
        $this->circlesService->savePlaces($circle, $request->places ?? [], Auth::user());

        // タグの保存
        try {
            $this->circlesService->saveTags(
                $circle,
                $request->tags ?? [],
                Auth::user()->can('staff.tags.edit'),
                Auth::user()
            );
        } catch (DenyCreateTagsException $e) {
            DB::rollBack();
            return redirect()
                ->route('staff.circles.create')
                ->withInput()
                ->withErrors(['tags' => $e->getMessage()]);
        }

        DB::commit();

        return redirect()
            ->back()
            ->with('topAlert.title', '企画情報を作成しました');
    }
}
