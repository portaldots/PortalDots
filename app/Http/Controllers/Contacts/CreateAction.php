<?php

namespace App\Http\Controllers\Contacts;

use App\Eloquents\Circle;
use App\Eloquents\ContactCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\Circles\SelectorService;

class CreateAction extends Controller
{
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

        if (Auth::user()->circles()->count() > 0 && empty($circle)) {
            return redirect()
                ->route('circles.selector.show', ['redirect_to' => route('contacts', null, false)]);
        }

        return view('contacts.form')
            ->with('circle', $circle)
            ->with('categories', ContactCategory::all());
    }
}
