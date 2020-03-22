<?php

namespace App\Http\Controllers\Users;

use App\Http\Requests\Users\ChangePasswordRequest;
use App\Services\Users\ChangePasswordService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PostChangePasswordAction extends Controller
{
    /**
     * @var ChangePasswordService
     */
    private $changePasswordService;

    public function __construct(ChangePasswordService $changePasswordService)
    {
        $this->changePasswordService = $changePasswordService;
    }

    public function __invoke(ChangePasswordRequest $request)
    {
        // ChangePasswordRequest クラス内で、現在のパスワードが正しいことも含めてのバリデーション済み

        $this->changePasswordService->changePassword(Auth::user(), $request->new_password);

        return redirect()->route('user.password')
            ->with('topAlert.title', 'パスワードを変更しました。');
    }
}
