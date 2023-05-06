<?php

namespace App\Http\Controllers\Circles;

use App\Http\Controllers\Controller;
use App\Eloquents\Circle;
use Illuminate\Http\Request;

class DoneAction extends Controller
{
    public function __invoke(Request $request, Circle $circle)
    {
        if (!$request->session()->has('done')) {
            return redirect()
                ->route('home');
        }

        $circle->load('participationType.form');

        $confirmationMessage = "";
        if (isset($circle->participationType->form->confirmation_message)) {
            $confirmationMessage = $circle->participationType->form->confirmation_message;
        }

        return view('circles.done')
            ->with('circle', $circle)
            ->with('confirmationMessage', $confirmationMessage)
            ->with('questions', $circle->participationType->form->questions);
    }
}
