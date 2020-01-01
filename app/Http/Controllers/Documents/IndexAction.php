<?php

namespace App\Http\Controllers\Documents;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eloquents\Document;

class IndexAction extends Controller
{
    public function __invoke()
    {
        return view('v2.documents.index')
            ->with('documents', Document::public()->with('schedule')->get());
    }
}
