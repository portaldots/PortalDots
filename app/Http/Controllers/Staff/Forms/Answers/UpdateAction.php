<?php

namespace App\Http\Controllers\Staff\Forms\Answers;

use Auth;
use App\Http\Controllers\Controller;
use App\Eloquents\Form;
use App\Eloquents\Circle;
use App\Eloquents\Answer;
use App\Http\Requests\Staff\Forms\AnswerRequest;
use App\Services\Forms\AnswersService;

class UpdateAction extends Controller
{
    private $answersService;

    public function __construct(AnswersService $answersService)
    {
        $this->answersService = $answersService;
    }

    public function __invoke(Form $form, Answer $answer, AnswerRequest $request)
    {
        // 回答に紐づく企画が参加登録未提出の場合、回答の更新を拒否する
        $answer->circle()->submitted()->firstOrFail();

        // スタッフによる修正は、リビジョンを記録するが確認状況(review_status)は変更しない
        $this->answersService->updateAnswer($form, $answer, $request, Auth::user(), true);
        if ($form->is_public && empty($form->participationType)) {
            // フォームが公開されている場合にのみ確認メールを送信する
            // ただし、参加登録フォームである場合は送信しない
            $this->answersService->sendAll($answer, Auth::user(), true);
        }
        return back()
            ->with('topAlert.title', '回答を更新しました');

        // return back()
        //         ->with('topAlert.type', 'danger')
        //         ->with('topAlert.title', '更新に失敗しました')
        //         ->with('topAlert.body', '恐れ入りますが、もう一度お試しください')
        //         ->withInput();
    }
}
