<?php

namespace App\Http\Controllers\Staff\Forms\Answers;

use App\Eloquents\Form;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Responders\Staff\GridResponder;
use App\GridMakers\AnswersGridMaker;

class ApiAction extends Controller
{
    /**
     * @var GridResponder
     */
    private $gridResponder;

    /**
     * @var AnswersGridMaker
     */
    private $answersGridMaker;

    public function __construct(
        GridResponder $gridResponder,
        AnswersGridMaker $answersGridMaker
    ) {
        $this->gridResponder = $gridResponder;
        $this->answersGridMaker = $answersGridMaker;
    }

    public function __invoke(Request $request, Form $form)
    {
        return $this->gridResponder
            ->setRequest($request)
            ->setGridMaker($this->answersGridMaker->withForm($form))
            ->response();
    }
}
