<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Eloquents\Page;
use App\Eloquents\Schedule;
use App\Eloquents\Document;
use App\Eloquents\Form;
use App\Eloquents\CustomForm;

class HomeAction extends Controller
{
    /**
     * 表示するお知らせ・配布資料の最大数
     */
    private const TAKE_COUNT = 5;

    public function __invoke()
    {
        return view('v2.home')
            ->with('circle_custom_form', CustomForm::getFormByType('circle'))
            ->with('my_circles', Auth::check()
                                    ? Auth::user()->circles()->withoutGlobalScope('approved')->get()
                                    : collect([]))
            ->with('pages', Page::take(self::TAKE_COUNT)->get())
            ->with('remaining_pages_count', max(Page::count() - self::TAKE_COUNT, 0))
            ->with('next_schedule', Schedule::startOrder()->notStarted()->first())
            // TODO: PortalDots では Auth::check() による配布資料表示の条件分岐を行わない
            ->with('documents', Auth::check()
                                    ? Document::take(self::TAKE_COUNT)->public()->with('schedule')->get()
                                    : collect([]))
            ->with('remaining_documents_count', Auth::check()
                                    ? max(Document::public()->count() - self::TAKE_COUNT, 0)
                                    : 0)
            ->with('forms', Form::take(self::TAKE_COUNT)->public()->open()->closeOrder()->get())
            ->with('remaining_forms_count', max(Form::public()->open()->count() - self::TAKE_COUNT, 0));
    }
}
