<?php

namespace App\Http\Controllers\Staff\Tags;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Tags\TagRequest;
use App\Eloquents\Tag;

class UpdateAction extends Controller
{
    public function __invoke(TagRequest $request, Tag $tag)
    {
        $tag->name = $request->validated()['name'];
        $tag->save();

        return redirect()
            ->route('staff.tags.edit', ['tag' => $tag])
            ->with('topAlert.title', 'タグを更新しました');
    }
}
