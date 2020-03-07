<?php

namespace App\Http\Controllers\Documents;

use Auth;
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

        // basename: ディレクトリ・トラバーサル脆弱性対策
        $path = config('portal.codeigniter_upload_dir') . '/documents/' . basename($document->filename);

        return response()->file($path);
    }
}
