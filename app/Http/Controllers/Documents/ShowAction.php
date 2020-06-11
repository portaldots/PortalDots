<?php

namespace App\Http\Controllers\Documents;

use Storage;
use App\Http\Controllers\Controller;
use App\Eloquents\Document;

class ShowAction extends Controller
{
    public function __invoke(Document $document)
    {
        if (!$document->is_public) {
            abort(404);
            return;
        }

        return response()->file(Storage::path($document->path));
    }
}
