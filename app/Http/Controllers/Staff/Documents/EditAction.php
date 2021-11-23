<?php

namespace App\Http\Controllers\Staff\Documents;

use App\Http\Controllers\Controller;
use App\Eloquents\Document;

class EditAction extends Controller
{
    public function __invoke(Document $document)
    {
        return view('staff.documents.form')
            ->with('document', $document);
    }
}
