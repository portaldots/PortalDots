<?php

namespace App\Http\Controllers\Auth\Password;

use App\Eloquents\User;
use App\Http\Requests\Auth\Password\ResetPasswordRequest;
use App\Services\Users\ChangePasswordService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PostResetPasswordAction extends Controller
{
    /**
     * @var ChangePasswordService
     */
    private $changePasswordService;

    public function __construct(ChangePasswordService $changePasswordService)
    {
        $this->changePasswordService = $changePasswordService;
    }

    public function __invoke(ResetPasswordRequest $request, User $user)
    {
        // signedミドルウェアが設定されていれば、$user は信頼できる
        $this->changePasswordService->changePassword($user, $request->new_password);

        return redirect()->route('login')
            ->with('topAlert.title', 'パスワードを変更しました。');
    }
}
