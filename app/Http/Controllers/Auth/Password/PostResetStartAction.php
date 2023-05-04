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
            ->with('topAlert.title', 'メールを確認してください')
            ->with('topAlert.body', '今から5分以内に再設定してください。もし届かない場合はもう一度お試しください。');
    }
}
