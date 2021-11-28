<?php

namespace App\Http\Controllers\Admin\Update;

use App\Http\Controllers\Controller;
use App\Services\Utils\ReleaseInfoService;

class IndexAction extends Controller
{
    /**
     * @var ReleaseInfoService
     */
    private $releaseInfoService;

    public function __construct(ReleaseInfoService $releaseInfoService)
    {
        $this->releaseInfoService = $releaseInfoService;
    }

    public function __invoke()
    {
        return view('admin.update.index')
            ->with('latest_release', $this->releaseInfoService->getReleaseOfLatestVersionWithinSameMajorVersion());
    }
}
