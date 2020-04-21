<?php

namespace App\Http\Controllers\Staff\Circles;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eloquents\Circle;
use App\Eloquents\CustomForm;
use App\Eloquents\Tag;

class EditAction extends Controller
{
    public function __invoke(Circle $circle)
    {
        if (!$circle->hasSubmitted()) {
            // 参加登録が未提出の企画の情報は閲覧・編集できない
            abort(404);
        }

        $circle->load('users');

        $member_ids = '';
        $members = $circle->users->filter(function ($user) {
            return !$user->pivot->is_leader;
        });
        foreach ($members as $member) {
            $member_ids .= $member->student_id . "\r\n";
        }

        return view('v2.staff.circles.form')
            ->with('custom_form', CustomForm::getFormByType('circle'))
            ->with('circle', $circle)
            ->with('leader', $circle->users->filter(function ($user) {
                return $user->pivot->is_leader;
            })->first())
            ->with('members', $member_ids)
            ->with('default_tags', $circle->tags->pluck('name')->map(function ($item) {
                return ['text' => $item];
            })->toJson())
            ->with('tags_autocomplete_items', Tag::get()->pluck('name')->map(function ($item) {
                return ['text' => $item];
            })->toJson());
    }
}
