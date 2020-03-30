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
        return redirect()->route('staff.circles.email')
            ->with('topAlert.title', '件名：「' . $request->title . '」を送信しました。');
    }
}
