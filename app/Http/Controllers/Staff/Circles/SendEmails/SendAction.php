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
        // 誰も所属していないサークルは参加登録方法が変更になったため存在しないと思うが、念の為バリデーションする
        // ここにメール送信処理を記述
        return redirect()->route('staff.circles.email', ['circle' => $circle])
            ->with('topAlert.title', "件名： {$request->title} を {$circle->name} に送信しました。");
    }
}
