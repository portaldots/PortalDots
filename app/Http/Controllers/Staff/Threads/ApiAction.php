<?php

namespace App\Http\Controllers\Staff\Threads;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Responders\Staff\GridResponder;
use App\GridMakers\ThreadsGridMaker;

class ApiAction extends Controller
{
    /**
     * @var GridResponder
     */
    private $gridResponder;

    /**
     * @var ThreadsGridMaker
     */
    private $threadsGridMaker;

    public function __construct(GridResponder $gridResponder, ThreadsGridMaker $threadsGridMaker)
    {
        $this->gridResponder = $gridResponder;
        $this->threadsGridMaker = $threadsGridMaker;
    }

    public function __invoke(Request $request)
    {
        return $this->gridResponder
            ->setRequest($request)
            ->setGridMaker($this->threadsGridMaker)
            ->response();
    }
}
