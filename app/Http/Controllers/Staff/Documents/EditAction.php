<?php

namespace App\Http\Controllers\Staff\Documents;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Eloquents\Document;

class EditAction extends Controller
{
    public function __invoke(Document $document)
    {
        return view('v2.staff.documents.form')
            ->with('document', $document);
    }
}
