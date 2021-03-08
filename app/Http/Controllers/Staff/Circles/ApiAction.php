<?php

namespace App\Http\Controllers\Staff\Circles;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Responders\Staff\GridResponder;
use App\GridMakers\CirclesGridMaker;

class ApiAction extends Controller
{
    /**
     * @var GridResponder
     */
    private $gridResponder;

    /**
     * @var CirclesGridMaker
     */
    private $circlesGridMaker;

    public function __construct(
        GridResponder $gridResponder,
        CirclesGridMaker $circlesGridMaker
    ) {
        $this->gridResponder = $gridResponder;
        $this->circlesGridMaker = $circlesGridMaker;
    }

    public function __invoke(Request $request)
    {
        return $this->gridResponder
            ->setRequest($request)
            ->setGridMaker($this->circlesGridMaker)
            ->response();
    }
}
