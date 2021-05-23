<?php

namespace App\Http\Controllers\Staff\Documents;

use App\Eloquents\Document;
use App\Services\Documents\DocumentsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DestroyAction extends Controller
{
    /**
     * @var DocumentsService
     */
    private $documentsService;

    public function __construct(DocumentsService $documentsService)
    {
        $this->documentsService = $documentsService;
    }

    public function __invoke(Document $document)
    {
        $this->documentsService->deleteDocument($document);

        return redirect()
            ->route('staff.documents.index')
            ->with('topAlert.title', '配布資料を削除しました');
    }
}
