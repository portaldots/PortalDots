<?php

namespace App\Http\Controllers\Staff\Forms\Answers\Uploads;

use Storage;
use Gate;
use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eloquents\Answer;
use App\Eloquents\AnswerDetail;

class ShowAction extends Controller
{
    public function __invoke(Request $request, int $form_id, Answer $answer, int $question_id)
    {
        // Form と Question については、DB から情報を取ってくる必要がないので、int で受け取る

        $file_path = AnswerDetail::select('answer')
            ->where('answer_id', $answer->id)
            ->where('question_id', $question_id)
            ->firstOrFail();

        return response()->file(Storage::path($file_path->answer));
    }
}
