<?php

namespace App\Http\Controllers\Forms\Answers\Uploads;

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
        $circle = $answer->circle()->withoutGlobalScope('approved')->first();
        if (Gate::denies('circle.belongsTo', $circle) && Auth::check() && !Auth::user()->is_staff) {
            // CodeIgniter 製スタッフモードからのダウンロードに対応するため、暫定的に
            // is_staff による判断を入れているが、スタッフモードの Laravel 化が完了したら
            // スタッフ用ファイルダウンロード用のアクションを別に作成する
            abort(404);
        }

        $file_path = AnswerDetail::select('answer')
            ->where('answer_id', $answer->id)
            ->where('question_id', $question_id)
            ->firstOrFail();

        return response()->file(Storage::path($file_path->answer));
    }
}
