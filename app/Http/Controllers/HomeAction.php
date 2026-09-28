<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Eloquents\Page;
use App\Eloquents\Document;
use App\Eloquents\Form;
use App\Eloquents\ParticipationType;
use App\Eloquents\Thread;
use App\Services\Circles\CircleProgressService;
use App\Services\Circles\SelectorService;

class HomeAction extends Controller
{
    /**
     * 表示するお知らせ・配布資料の最大数
     */
    private const TAKE_COUNT = 5;

    /**
     * @var SelectorService
     */
    private $selectorService;

    /**
     * @var CircleProgressService
     */
    private $circleProgressService;

    public function __construct(SelectorService $selectorService, CircleProgressService $circleProgressService)
    {
        $this->selectorService = $selectorService;
        $this->circleProgressService = $circleProgressService;
    }

    public function __invoke()
    {
        $circle = $this->selectorService->getCircle();
        $user = Auth::user();

        if (isset($circle)) {
            $circle->loadMissing(['places', 'participationType']);
        }

        // ログイン中かつ企画が選択されている場合のみ、「対応が必要なもの」を計算する
        $showsCircleActionItems = Auth::check() && isset($circle);

        return view('home')
            ->with('shows_circle_action_items', $showsCircleActionItems)
            ->with(
                'circle_progress',
                $showsCircleActionItems ? $this->circleProgressService->forCircle($circle) : null
            )
            ->with(
                'circle_thread',
                $showsCircleActionItems
                    ? Thread::where('circle_id', $circle->id)
                        ->where('status', Thread::STATUS_AWAITING_REPLY)
                        ->first()
                    : null
            )
            ->with('participation_types', ParticipationType::open()->public()->get())
            ->with(
                'my_circles',
                Auth::check()
                    ? Auth::user()
                    ->circles()
                    ->with(['participationType', 'participationType.form'])
                    ->get()
                    : collect([])
            )
            ->with('circle', $circle)
            ->with(
                'pinned_pages',
                Page::visibleTo($user, $circle)
                    ->with([
                        'viewableTags',
                        'documents' => function ($query) use ($user, $circle) {
                            $query->visibleTo($user, $circle);
                        }
                    ])
                    ->pinned()
                    ->get()
            )
            ->with(
                'pages',
                Page::visibleTo($user, $circle)
                    ->take(self::TAKE_COUNT)
                    ->with([
                        'viewableTags',
                        'usersWhoRead' => function ($query) {
                            $query->where('user_id', Auth::id());
                        },
                    ])
                    ->pinned(false)
                    ->get()
            )
            ->with(
                'documents',
                Document::visibleTo($user, $circle)
                    ->take(self::TAKE_COUNT)
                    ->get()
            )
            ->with(
                'forms',
                Form::byCircle($circle)
                    ->take(self::TAKE_COUNT)
                    ->public()
                    ->open()
                    ->withoutParticipationForms()
                    ->closeOrder()
                    ->get()
            );
    }
}
