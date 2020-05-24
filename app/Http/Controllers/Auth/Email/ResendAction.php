<?php

namespace App\Http\Controllers\Auth\Email;

use App\Services\Auth\EmailService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ResendAction extends Controller
{
    /**
     * @var EmailService
     */
    private $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    public function __invoke()
    {
        $this->emailService->sendAll(Auth::user());
        return redirect()
            ->route('verification.notice')
            ->with('topAlert.title', '確認メールを再送しました。');
    }
}
