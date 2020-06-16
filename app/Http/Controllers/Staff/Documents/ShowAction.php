<?php

namespace App\Http\Controllers\Staff\Documents;

use Storage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Eloquents\Document;

class ShowAction extends Controller
{
    public function __invoke(Document $document)
    {
        return response()->file(Storage::path($document->path));
    }
}
