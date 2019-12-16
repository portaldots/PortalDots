<?php

namespace App\Http\Controllers\Staff\SendEmails;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Eloquents\Email;

class DestroyAction extends Controller
{
    public function __invoke()
    {
        Email::query()->delete();

        return redirect()
            ->route('staff.send_emails')
            ->with('toast', '一斉メール送信をキャンセルしました');
    }
}
