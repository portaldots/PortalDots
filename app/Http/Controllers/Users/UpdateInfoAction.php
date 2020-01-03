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
        $changed_email = false;
        $changed_univemail = false;
        $user = User::find(Auth::id());
        if ($user->email !== $request->email) {
            $user->email = $request->email;
            $user->email_verified_at = null;
            $changed_email = true;
        }
        if (!empty($request->student_id)) {
            if ($user->student_id !== $request->student_id) {
                $user->student_id = $request->student_id;
                $user->univemail_verified_at = null;
                $changed_univemail = true;
            }
        }

        if (!empty($request->name)) {
            $user->name = $request->name;
        }

        if (!empty($request->name_yomi)) {
            $user->name_yomi = $request->name_yomi;
        }

        $user->tel = $request->tel;

        if (!$user->save()) {
            return redirect()
                ->route('user.edit')
                ->with('error_message', 'ユーザー情報の更新に失敗しました')
                ->withInput();
        }

        if ($user->univemail === $user->email) {
            $this->verifyService->markEmailAsVerified($user, $user->email);
        }

        if ($changed_email) {
            $this->emailService->sendToEmail($user, $user->is_signed_up);
        }

        if ($changed_univemail) {
            $this->emailService->sendToUnivemail($user, $user->is_signed_up);
        }

        if ($changed_univemail || $changed_email) {
            return redirect('/')
            ->with('success_message', '確認メールを送信しました');
        }

        if ($user->areBothEmailsVerified()) {
            return redirect()
            ->route('user.edit')
            ->with('success_message', 'ユーザー情報を更新しました');
        }

        return redirect('/')
            ->with('success_message', 'ユーザー情報を更新しました');
    }
}
