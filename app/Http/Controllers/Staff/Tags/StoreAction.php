<?php

namespace App\Http\Controllers\Staff\Tags;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Tags\TagRequest;
use App\Eloquents\Tag;

class StoreAction extends Controller
{
    public function __invoke(TagRequest $request)
    {
        Tag::create([
            'name' => $request->validated()['name'],
        ]);

        return redirect()
            ->route('staff.tags.create')
            ->with('topAlert.title', 'タグを作成しました');
    }
}
