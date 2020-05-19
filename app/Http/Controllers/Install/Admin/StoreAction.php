<?php

namespace App\Http\Controllers\Install\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Install\AdminRequest;
use App\Services\Auth\RegisterService;
use App\Services\Install\RunInstallService;
use App\Eloquents\User;

class StoreAction extends Controller
{
    /**
     * @var RegisterService
     */
    private $registerService;

    /**
     * @var RunInstallService
     */
    private $runInstallService;

    public function __construct(
        RegisterService $registerService,
        RunInstallService $runInstallService
    ) {
        $this->registerService = $registerService;
        $this->runInstallService = $runInstallService;
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

            return redirect('/')
                ->with('topAlert.keepVisible', true)
                ->with('topAlert.title', 'インストールが完了しました！')
                ->with('topAlert.body', 'まず、作成した管理者ユーザーの学籍番号とパスワードでログインしましょう');
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
