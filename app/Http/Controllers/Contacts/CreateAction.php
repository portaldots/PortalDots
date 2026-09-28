<?php

namespace App\Http\Controllers\Contacts;

use App\Eloquents\ContactCategory;
use App\Eloquents\Thread;
use App\Eloquents\ThreadEntry;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Services\Circles\SelectorService;

class CreateAction extends Controller
{
    /**
     * @var SelectorService
     */
    private $selectorService;

    public function __construct(SelectorService $selectorService)
    {
        $this->selectorService = $selectorService;
    }

    public function __invoke()
    {
        $circle = $this->selectorService->getCircle();

        // 会話はまだ何もやり取りが無い段階では作成しない（初回投稿時に遅延作成する）
        $thread = isset($circle)
            ? Thread::where('circle_id', $circle->id)->first()
            : Thread::whereNull('circle_id')->where('user_id', Auth::id())->first();

        $entries = isset($thread)
            ? $thread->entries()
                ->where('kind', '!=', ThreadEntry::KIND_INTERNAL_NOTE)
                ->with(['author', 'contactCategory', 'attachments'])
                ->get()
            : collect();

        return view('contacts.show')
            ->with('circle', $circle)
            ->with('thread', $thread)
            ->with('entries', $entries)
            ->with('categories', ContactCategory::all())
            ->with('clientToken', (string)Str::uuid());
    }
}
