<?php

namespace App\Http\Controllers\Staff\Documents;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Services\Documents\DocumentsService;
use App\Http\Requests\Staff\Documents\UpdateDocumentRequest;
use App\Eloquents\Schedule;
use App\Eloquents\Document;

class UpdateAction extends Controller
{
    /**
     * @var DocumentsService
     */
    private $documentsService;

    public function __construct(DocumentsService $documentsService)
    {
        $this->documentsService = $documentsService;
    }

    public function __invoke(UpdateDocumentRequest $request, Document $document)
    {
        $validated = $request->validated();

        $this->documentsService->updateDocument(
            $document,
            $validated['name'],
            $validated['description'],
            Auth::user(),
            (bool)$validated['is_public'],
            (bool)$validated['is_important'],
            !empty($validated['schedule_id']) ? Schedule::findOrFail($validated['schedule_id']) : null,
            $validated['notes']
        );

        return redirect()
            ->route('staff.documents.edit', ['document' => $document])
            ->with('topAlert.title', '配布資料を更新しました');
    }
}
