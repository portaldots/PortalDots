<?php

namespace App\Http\Controllers\Staff\Forms\Answers;

use App\Eloquents\Form;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Responders\Staff\GridResponder;
use App\GridMakers\FormsGridMaker;

class ApiAction extends Controller
{
    /**
     * @var GridResponder
     */
    private $gridResponder;

    /**
     * @var FormsGridMaker
     */
    private $formsGridMaker;

    public function __construct(
        GridResponder $gridResponder,
        FormsGridMaker $formsGridMaker
    ) {
        $this->gridResponder = $gridResponder;
        $this->formsGridMaker = $formsGridMaker;
    }

    public function __invoke(Request $request, Form $form)
    {
        return $this->gridResponder
            ->setRequest($request)
            ->setGridMaker($this->formsGridMaker)
            ->response();
    }
}
