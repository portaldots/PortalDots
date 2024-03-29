<?php

namespace App\Http\Controllers\Circles;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Eloquents\ParticipationType;

class CreateAction extends Controller
{
    public function __invoke(Request $request)
    {
        if (empty($request->participation_type)) {
            abort(404);
        }

        $participationType = ParticipationType::findOrFail($request->participation_type);

        $this->authorize('circle.create', $participationType);

        return view('circles.form')
            ->with('participation_type', $participationType)
            ->with('form', $participationType->form)
            ->with('questions', $participationType->form->questions()->get());
    }
}
