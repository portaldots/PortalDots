<?php

namespace App\Http\Controllers\Users;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\ChangeInfoRequest;
use App\Services\Auth\EmailService;
use App\Services\Auth\VerifyService;
use Illuminate\Support\Facades\Auth;
use App\Eloquents\User;
use Swift_RfcComplianceException;

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
        if (!empty($request->univemail_local_part) || !empty($request->univemail_domain_part)) {
            if (
                $user->univemail_local_part !== $request->univemail_local_part ||
                $user->univemail_domain_part !== $request->univemail_domain_part
            ) {
                $user->univemail_local_part = $request->univemail_local_part;
                $user->univemail_domain_part = $request->univemail_domain_part;
                $user->univemail_verified_at = null;
                $user->is_verified_by_staff = false;
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

        if ($user->univemail === $user->email && !$user->is_verified_by_staff) {
            $this->verifyService->markEmailAsVerified($user, $user->email);
        }

        try {
            if ($changed_email) {
                $this->emailService->sendToEmail($user);
            }

            if ($changed_univemail) {
                $this->emailService->sendToUnivemail($user);
            }
        } catch (Swift_RfcComplianceException $e) {
            return redirect()
                ->route('user.edit')
                ->withInput()
                ->withErrors(['student_id' => config('portal.student_id_name') . 'を正しく入力してください']);
        }

        if (!$user->save()) {
            return redirect()
                ->route('user.edit')
                ->with('topAlert.type', 'danger')
                ->with('topAlert.title', 'ユーザー情報の更新に失敗しました')
                ->withInput();
        }

        if ($changed_univemail || $changed_email) {
            return redirect()
                ->route('verification.notice')
                ->with('topAlert.title', '確認メールを送信しました');
        }

        return redirect()
            ->route('user.edit')
            ->with('topAlert.title', 'ユーザー情報を更新しました');
    }
}
