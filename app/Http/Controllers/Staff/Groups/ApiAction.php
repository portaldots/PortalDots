<?php

namespace App\Http\Controllers\Staff\Groups;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Responders\Staff\GridResponder;
use App\GridMakers\GroupsGridMaker;

class ApiAction extends Controller
{
    /**
     * @var GridResponder
     */
    private $gridResponder;

    /**
     * @var GroupsGridMaker
     */
    private $groupsGridMaker;

    public function __construct(
        GridResponder $gridResponder,
        GroupsGridMaker $groupsGridMaker
    ) {
        $this->gridResponder = $gridResponder;
        $this->groupsGridMaker = $groupsGridMaker;
    }

    public function __invoke(Request $request)
    {
        return $this->gridResponder
            ->setRequest($request)
            ->setGridMaker($this->groupsGridMaker)
            ->response();
    }
}
