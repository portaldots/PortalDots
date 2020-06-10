<?php

namespace App\Http\Controllers\Documents;

use Auth;
use Storage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eloquents\Document;

class ShowAction extends Controller
{
    public function __invoke(Document $document)
    {
        if (!$document->is_public && (!Auth::check() || !Auth::user()->is_staff)) {
            abort(404);
            return;
        }

        return response()->file(Storage::path($document->path));
    }
}
