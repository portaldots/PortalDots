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

    // カスタムフォームの場合、AnswerRequest の $this->route('form') で
    // 現在のフォームを取得することができない
    // →グローバルスコープを使うのをやめる
    public function __invoke(int $form_id, Answer $answer, AnswerRequest $request)
    {
        // カスタムフォームの編集はできるようにする
        $form = Form::withoutGlobalScope('withoutCustomForms')->findOrFail($form_id);

        $this->answersService->updateAnswer($form, $answer, $request);
        if ($form->is_public && empty($form->customForm)) {
            // フォームが公開されている場合にのみ確認メールを送信する
            // ただし、カスタムフォームである場合は送信しない
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
