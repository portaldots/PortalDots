<?php

namespace App\Http\Controllers\Auth\Password;

use App\Http\Requests\Auth\Password\ResetStartRequest;
use App\Services\Auth\ResetPasswordService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PostResetStartAction extends Controller
{
    /**
     * @var ResetPasswordService
     */
    private $resetPasswordService;

    public function __construct(ResetPasswordService $resetPasswordService)
    {
        $this->resetPasswordService = $resetPasswordService;
    }

    public function __invoke(ResetStartRequest $request)
    {
        $this->resetPasswordService->handleResetStart($request->login_id);

        return redirect()->route('password.request')
            ->with('success_message', 'パスワード再設定に関するご案内を連絡先メールアドレス宛に送信しました。メール送信から 5 分以内に再設定を完了してください。
                もし届かない場合、もう一度お試しください。');
    }
}
