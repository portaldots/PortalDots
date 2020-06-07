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

        return view('v2.home')
            ->with('circle_custom_form', CustomForm::getFormByType('circle'))
            ->with('my_circles', Auth::check()
                                    ? Auth::user()->circles()->get()
                                    : collect([]))
            ->with('pages', Page::byCircle($circle)->take(self::TAKE_COUNT)->get())
            ->with('remaining_pages_count', max(Page::count() - self::TAKE_COUNT, 0))
            ->with('next_schedule', Schedule::startOrder()->notStarted()->first())
            ->with('documents', Document::take(self::TAKE_COUNT)->public()->with('schedule')->get())
            ->with('remaining_documents_count', Auth::check()
                                    ? max(Document::public()->count() - self::TAKE_COUNT, 0)
                                    : 0)
            ->with('forms', Form::take(self::TAKE_COUNT)->public()->open()->withoutCustomForms()->closeOrder()->get())
            ->with('remaining_forms_count', max(Form::public()->open()->count() - self::TAKE_COUNT, 0));
    }
}
