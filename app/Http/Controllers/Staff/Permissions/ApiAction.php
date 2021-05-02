<?php

namespace App\Http\Controllers\Staff\Permissions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Responders\Staff\GridResponder;
use App\GridMakers\PermissionsGridMaker;

class ApiAction extends Controller
{
    /**
     * @var GridResponder
     */
    private $gridResponder;

    /**
     * @var PermissionsGridMaker
     */
    private $permissionsGridMaker;

    public function __construct(
        GridResponder $gridResponder,
        PermissionsGridMaker $permissionsGridMaker
    ) {
        $this->gridResponder = $gridResponder;
        $this->permissionsGridMaker = $permissionsGridMaker;
    }

    public function __invoke(Request $request)
    {
        return $this->gridResponder
            ->setRequest($request)
            ->setGridMaker($this->permissionsGridMaker)
            ->response();
    }
}
