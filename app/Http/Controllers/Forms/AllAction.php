<?php

namespace App\Http\Controllers\Forms;

use App\Eloquents\Circle;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eloquents\Form;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Services\Circles\SelectorService;

class AllAction extends Controller
{
    /**
     * @var SelectorService
     */
    private $selectorService;

    public function __construct(SelectorService $selectorService)
    {
        $this->selectorService = $selectorService;
    }

    public function __invoke(Request $request)
    {
        $forms = Form::public()->withoutCustomForms()->closeOrder()->paginate(10);

        // TODO: 所属している企画が存在しない場合、エラーを表示する

        if ($forms->currentPage() > $forms->lastPage()) {
            return redirect($forms->url($forms->lastPage()));
        }

        return view('v2.forms.list')
            ->with('forms', $forms)
            ->with('circle', $this->selectorService->getCircle());
    }
}
