<?php

namespace App\Http\Controllers\Forms\Answers\Uploads;

use Storage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eloquents\AnswerDetail;

class ShowAction extends Controller
{
    public function __invoke(Request $request, int $form, int $answer, int $question)
    {
        if (! $request->hasValidSignature()) {
            abort(401);
        }

        $file_path = AnswerDetail::select('answer')
            ->where('answer_id', $answer)
            ->where('question_id', $question)
            ->first();

        return Storage::download($file_path->answer);
    }
}
