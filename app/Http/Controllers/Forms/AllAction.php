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
        $circle = $this->selectorService->getCircle();

        $forms = Form::byCircle($circle)->public()->withoutCustomForms()->closeOrder()->paginate(10);

        if (empty($this->selectorService->getCircle())) {
            // TODO: もうちょっとまともなエラー表示にする
            return redirect()
                ->route('home')
                ->with('topAlert.type', 'danger')
                ->with('topAlert.title', '企画に所属していないため、このページにアクセスできません');
        }

        if ($forms->currentPage() > $forms->lastPage()) {
            return redirect($forms->url($forms->lastPage()));
        }

        return view('forms.list')
            ->with('forms', $forms)
            ->with('circle', $this->selectorService->getCircle());
    }
}
