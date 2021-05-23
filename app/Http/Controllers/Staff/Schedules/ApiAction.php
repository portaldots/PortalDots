<?php

namespace App\Http\Controllers\Staff\Schedules;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Responders\Staff\GridResponder;
use App\GridMakers\SchedulesGridMaker;

class ApiAction extends Controller
{
    /**
     * @var GridResponder
     */
    private $gridResponder;

    /**
     * @var SchedulesGridMaker
     */
    private $schedulesGridMaker;

    public function __construct(
        GridResponder $gridResponder,
        SchedulesGridMaker $schedulesGridMaker
    ) {
        $this->gridResponder = $gridResponder;
        $this->schedulesGridMaker = $schedulesGridMaker;
    }

    public function __invoke(Request $request)
    {
        return $this->gridResponder
            ->setRequest($request)
            ->setGridMaker($this->schedulesGridMaker)
            ->response();
    }
}
