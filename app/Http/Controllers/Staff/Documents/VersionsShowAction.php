<?php

namespace App\Http\Controllers\Staff\Documents;

use Storage;
use App\Http\Controllers\Controller;
use App\Eloquents\Document;
use App\Eloquents\DocumentVersion;

class VersionsShowAction extends Controller
{
    public function __invoke(Document $document, DocumentVersion $version)
    {
        return response()->file(Storage::path($version->path));
    }
}
