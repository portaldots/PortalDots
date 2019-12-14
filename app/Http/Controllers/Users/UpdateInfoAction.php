<?php

namespace App\Http\Controllers\Users;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\ChangeInfoRequest;
use App\Services\Auth\EmailService;
use App\Services\Auth\VerifyService;
use Illuminate\Support\Facades\Auth;
use App\Eloquents\User;

class UpdateInfoAction extends Controller
{
    public function __construct(EmailService $emailService, VerifyService $verifyService)
    {
        $this->emailService = $emailService;
        $this->verifyService = $verifyService;
    }

    public function __invoke(ChangeInfoRequest $request)
    {
        $email_flag = false;
        $univemail_flag = false;
        $user = User::find(Auth::id());
        if ($user->email !== $request->email) {
            $user->email = $request->email;
            $user->email_verified_at = null;
            $email_flag = true;
        }
        if (!empty($request->student_id)) {
            if ($user->student_id !== $request->student_id) {
                $user->student_id = $request->student_id;
                $user->univemail_verified_at = null;
                $univemail_flag = true;
            }
        }

        if (!empty($request->name)) {
            $user->name = $request->name;
        }

        if (!empty($request->name_yomi)) {
            $user->name_yomi = $request->name_yomi;
        }
        
        $user->tel = $request->tel;

        $user->save();

        if ($user->univemail === $user->email) {
            $this->verifyService->markEmailAsVerified($user, $user->email);
        }

        if ($email_flag) {
            $this->emailService->sendToEmail($user);
        }

        if ($univemail_flag) {
            $this->emailService->sendToUnivemail($user);
        }

        if (!($user->areBothEmailsVerified())) {
            return redirect()
            ->route('home')
            ->with('success_message', '確認メールを送信しました');
        }
        return redirect()
            ->route('user.edit')
            ->with('success_message', 'ユーザー情報を更新しました');
    }
}
