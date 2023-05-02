<?php

namespace App\Http\Controllers\Staff\Circles\ParticipationTypes;

use App\Eloquents\ParticipationType;
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

    public function __invoke(Request $request, ParticipationType $participationType)
    {
        return $this->gridResponder
            ->setRequest($request)
            ->setGridMaker(
                $this->circlesGridMaker
                    ->withParticipationType($participationType)
            )
            ->response();
    }
}
