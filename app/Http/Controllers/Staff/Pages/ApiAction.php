<?php

namespace App\Http\Controllers\Staff\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Responders\Staff\GridResponder;
use App\GridMakers\PagesGridMaker;

class ApiAction extends Controller
{
    /**
     * @var GridResponder
     */
    private $gridResponder;

    /**
     * @var PagesGridMaker
     */
    private $pagesGridMaker;

    public function __construct(
        GridResponder $gridResponder,
        PagesGridMaker $pagesGridMaker
    ) {
        $this->gridResponder = $gridResponder;
        $this->pagesGridMaker = $pagesGridMaker;
    }

    public function __invoke(Request $request)
    {
        return $this->gridResponder
            ->setRequest($request)
            ->setGridMaker($this->pagesGridMaker)
            ->response();
    }
}
