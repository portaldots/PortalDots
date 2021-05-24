<?php

namespace App\Http\Controllers\Admin\ActivityLog;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Responders\Staff\GridResponder;
use App\GridMakers\ActivityLogGridMaker;

class ApiAction extends Controller
{
    /**
     * @var GridResponder
     */
    private $gridResponder;

    /**
     * @var ActivityLogGridMaker
     */
    private $activityLogGridMaker;

    public function __construct(
        GridResponder $gridResponder,
        ActivityLogGridMaker $activityLogGridMaker
    ) {
        $this->gridResponder = $gridResponder;
        $this->activityLogGridMaker = $activityLogGridMaker;
    }

    public function __invoke(Request $request)
    {
        return $this->gridResponder
            ->setRequest($request)
            ->setGridMaker($this->activityLogGridMaker)
            ->response();
    }
}
