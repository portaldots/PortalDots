<?php

namespace App\Http\Controllers\Install\Admin;

use Illuminate\Auth\Events\Registered;
use App\Http\Controllers\Controller;
use App\Http\Requests\Install\AdminRequest;
use App\Services\Auth\RegisterService;
use App\Services\Auth\EmailService;
use App\Services\Install\RunInstallService;
use App\Eloquents\User;
use Auth;

class StoreAction extends Controller
{
    /**
     * @var RegisterService
     */
    private $registerService;

    /**
     * @var EmailService
     */
    private $emailService;

    /**
     * @var RunInstallService
     */
    private $runInstallService;

    /**
     * @var VerifyService
     */
    private $verifyService;

    public function __construct(
        RegisterService $registerService,
        EmailService $emailService,
        RunInstallService $runInstallService,
        VerifyService $verifyService
    ) {
        $this->registerService = $registerService;
        $this->emailService = $emailService;
        $this->runInstallService = $runInstallService;
        $this->verifyService = $verifyService;
    }

    public function __invoke(AdminRequest $request)
    {
        try {
            $this->runInstallService->run();

            $user = $this->registerService->create(
                $request->student_id,
                $request->name,
                $request->name_yomi,
                $request->email,
                $request->tel,
                $request->password
            );

            $user->is_staff = true;
            $user->is_admin = true;
            $user->save();

            event(new Registered($user));

            // メール認証に関する処理
            if ($user->univemail === $user->email) {
                $this->verifyService->markEmailAsVerified($user, $user->email);
            }
            $this->emailService->sendAll($user);

            Auth::login($user);

            return redirect()
                ->route('verification.notice')
                ->with('topAlert.keepVisible', true)
                ->with('topAlert.title', 'インストールが完了しました！')
                ->with('topAlert.body', '最後に、管理者ユーザーのメール認証を行ってください');
        } catch (\Exception $e) {
            $this->runInstallService->rollback();

            return redirect()
                ->back()
                ->withInput()
                ->with('topAlert.type', 'danger')
                ->with('topAlert.title', '不明なエラーが発生しました');
        }
    }
}
