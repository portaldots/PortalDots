<?php

namespace App\Http\Controllers\Staff\Documents;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Responders\Staff\GridResponder;
use App\GridMakers\DocumentsGridMaker;

class ApiAction extends Controller
{
    /**
     * @var GridResponder
     */
    private $gridResponder;

    /**
     * @var DocumentsGridMaker
     */
    private $documentsGridMaker;

    public function __construct(
        GridResponder $gridResponder,
        DocumentsGridMaker $documentsGridMaker
    ) {
        $this->gridResponder = $gridResponder;
        $this->documentsGridMaker = $documentsGridMaker;
    }

    public function __invoke(Request $request)
    {
        return $this->gridResponder
            ->setRequest($request)
            ->setGridMaker($this->documentsGridMaker)
            ->response();
    }
}
