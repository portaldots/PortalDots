<?php

namespace App\Http\Controllers\Staff\Documents;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Services\Documents\DocumentsService;
use App\Http\Requests\Staff\Documents\DocumentRequest;
use App\Eloquents\Schedule;

class StoreAction extends Controller
{
    /**
     * @var DocumentsService
     */
    private $documentsService;

    public function __construct(DocumentsService $documentsService)
    {
        $this->documentsService = $documentsService;
    }

    public function __invoke(DocumentRequest $request)
    {
        $validated = $request->validated();

        $this->documentsService->createDocument(
            $validated['name'],
            $validated['description'],
            $request->file('file'),
            Auth::user(),
            (bool)$validated['is_public'],
            (bool)$validated['is_important'],
            !empty($validated['schedule_id']) ? Schedule::findOrFail($validated['schedule_id']) : null,
            $validated['notes']
        );
    }
}
