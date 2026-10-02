<?php

declare(strict_types=1);

namespace App\Http\Controllers\Staff\Progress;

use App\Eloquents\Circle;
use App\Eloquents\Thread;
use App\Http\Controllers\Controller;
use App\Services\Circles\CircleProgressService;
use App\Services\Circles\ValueObjects\ProgressUnit;
use Illuminate\Pagination\LengthAwarePaginator;

class IndexAction extends Controller
{
    private const PER_PAGE = 20;

    /**
     * @var CircleProgressService
     */
    private $circleProgressService;

    public function __construct(CircleProgressService $circleProgressService)
    {
        $this->circleProgressService = $circleProgressService;
    }

    public function __invoke()
    {
        $circles = Circle::approved()->with('tags')->orderBy('name_yomi')->get();
        $progressByCircleId = $this->circleProgressService->forCircles($circles);
        $progresses = $circles->map(function (Circle $circle) use ($progressByCircleId) {
            return $progressByCircleId->get($circle->id);
        });

        $page = LengthAwarePaginator::resolveCurrentPage();
        $paginatedProgresses = new LengthAwarePaginator(
            $progresses->forPage($page, self::PER_PAGE)->values(),
            $progresses->count(),
            self::PER_PAGE,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        // 全企画のうち、レビュー待ちの単位（要スタッフ対応）だけを抽出する
        $reviewEntries = $progresses->flatMap(function ($progress) {
            return $progress->unitsInState([ProgressUnit::STATE_REVIEW])
                ->map(function (ProgressUnit $unit) use ($progress) {
                    return ['circle' => $progress->getCircle(), 'unit' => $unit];
                });
        });

        $needsStaffThreads = Thread::where('status', Thread::STATUS_NEEDS_STAFF)
            ->whereIn('circle_id', $circles->pluck('id')->all())
            ->with('circle')
            ->get();

        return view('staff.progress.index')
            ->with('progresses', $paginatedProgresses)
            ->with('review_entries', $reviewEntries)
            ->with('needs_staff_threads', $needsStaffThreads);
    }
}
