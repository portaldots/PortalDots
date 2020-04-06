<?php

namespace App\Http\Controllers\Staff\Circles\SendEmails;

use App\Eloquents\Circle;
use App\Http\Controllers\Controller;
use App\Http\Requests\Circles\SendEmailsRequest;
use Illuminate\Http\Request;

class SendAction extends Controller
{
    public function __invoke(Circle $circle, SendEmailsRequest $request)
    {
        if (count($circle->users()->get()) === 0) {
            return redirect()
                ->route('staff.circles.email', ['circle' => $circle])
                ->with('topAlert.title', '送信に失敗しました')
                ->with('topAlert.type', 'danger')
                ->with('topAlert.body', 'この企画に登録されているユーザーが存在しません。')
                ->withInput();
        }
        
        // ここにメール送信処理を記述
        return redirect()->route('staff.circles.email', ['circle' => $circle])
            ->with('topAlert.title', "件名：「{$request->title}」を {$circle->name} に送信しました。");
    }
}
