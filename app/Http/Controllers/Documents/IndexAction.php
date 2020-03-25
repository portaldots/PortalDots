<?php

namespace App\Http\Controllers\Documents;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eloquents\Document;

class IndexAction extends Controller
{
    public function __invoke()
    {
        $documents = Document::public()->with('schedule')->paginate(10);

        if ($documents->currentPage() > $documents->lastPage()) {
            return redirect($documents->url($documents->lastPage()));
        }

        return view('v2.documents.index')
            ->with('documents', $documents);
    }
}
