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
        return view('v2.contacts.form')
            ->with('circle', $this->selectorService->getCircle())
            ->with('categories', ContactCategory::all());
    }
}
