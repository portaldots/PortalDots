<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Eloquents\Page;
use App\Eloquents\Schedule;
use App\Eloquents\Document;
use App\Eloquents\Form;
use App\Eloquents\CustomForm;
use App\Services\Circles\SelectorService;

class HomeAction extends Controller
{
    /**
     * 表示するお知らせ・配布資料の最大数
     */
    private const TAKE_COUNT = 5;

    /**
     * @var SelectorService
     */
    private $selectorService;

    public function __construct(SelectorService $selectorService)
    {
        $this->selectorService = $selectorService;
    }

    public function __invoke()
    {
        $circle = $this->selectorService->getCircle();

        return view('home')
            ->with('circle_custom_form', CustomForm::getFormByType('circle'))
            ->with('my_circles', Auth::check()
                                    ? Auth::user()->circles()->get()
                                    : collect([]))
            ->with('pages', Page::byCircle($circle)->take(self::TAKE_COUNT)->with(['usersWhoRead' => function ($query) {
                $query->where('user_id', Auth::id());
            }])->get())
            ->with('next_schedule', Schedule::startOrder()->notStarted()->first())
            ->with('documents', Document::take(self::TAKE_COUNT)->public()->with('schedule')->get())
            ->with('forms', Form::byCircle($circle)->take(self::TAKE_COUNT)
                ->public()->open()->withoutCustomForms()->closeOrder()->get());
    }
}
