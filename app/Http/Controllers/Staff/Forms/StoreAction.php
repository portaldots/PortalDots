<?php

declare(strict_types=1);

namespace App\Http\Controllers\Staff\Forms;

use App\Eloquents\Form;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\Forms\FormRequest;
use App\Services\Forms\FormsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StoreAction extends Controller
{
    /**
     * @var FormsService
     */
    private $formsService;

    public function __construct(FormsService $formsService)
    {
        $this->formsService = $formsService;
    }

    public function __invoke(FormRequest $request)
    {
        $values = $request->validated();

        return DB::transaction(function () use ($values) {
            $form = $this->formsService->createForm(
                $values['name'],
                $values['description'] ?? '',
                $values['confirmation_message'] ?? '',
                new Carbon($values['open_at']),
                new Carbon($values['close_at']),
                Auth::user(),
                (int)$values['max_answers'] ?? 1,
                isset($values['is_public']) && $values['is_public'] === "1",
                $values['answerable_tags'] ?? []
            );

            return redirect()
                ->route('staff.forms.editor', ['form' => $form]);
        });
    }
}
