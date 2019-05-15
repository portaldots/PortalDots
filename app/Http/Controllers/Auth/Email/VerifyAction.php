<?php

namespace App\Http\Controllers\Auth\Email;

use App\Eloquents\User;
use App\Services\Auth\VerifyService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class VerifyAction extends Controller
{
    private $verifyService;

    public function __construct(VerifyService $verifyService)
    {
        $this->verifyService = $verifyService;
    }

    public function __invoke(Request $request, $type, User $user)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'この URL は無効です。');
        }

        $result = false;
        switch ($type) {
            case 'email':
                $result = $this->verifyService->markEmailAsVerified($user);
                break;
            case 'univemail':
                $result = $this->verifyService->markUnivemailAsVerified($user);
                break;
            default:
                abort(404);
        }

        Auth::login($user);

        $response = redirect()
            ->route($user->areBothEmailsVerified() ? 'verification.completed' : 'verification.notice');

        if ($result) {
            return $response->with('success_message', 'メール認証に成功しました。');
        } else {
            return $response->with('error_message', 'この URL は使用済みです。');
        }
    }
}
