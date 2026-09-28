<?php

namespace App\Http\Controllers\Staff\Documents;

use App\Http\Controllers\Controller;
use App\Services\Documents\DocumentsService;
use App\Http\Requests\Staff\Documents\CreateDocumentRequest;
use Illuminate\Support\Facades\Auth;

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

    public function __invoke(CreateDocumentRequest $request)
    {
        $validated = $request->validated();

        $this->documentsService->createDocument(
            $validated['name'],
            $validated['description'],
            $request->file('file'),
            (bool)$validated['is_public'],
            (bool)$validated['is_important'],
            $validated['notes'],
            $validated['audience'],
            $validated['viewable_tags'] ?? [],
            $validated['viewable_circles'] ?? [],
            Auth::user()
        );

        return redirect()
            ->route('staff.documents.create')
            ->with('topAlert.title', '配布資料を作成しました');
    }
}
