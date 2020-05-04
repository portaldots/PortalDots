<?php

namespace App\Http\Controllers\Install\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
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

    public function __invoke(RegisterRequest $request)
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
                ->with('topAlert.title', 'インストール完了しました！');
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
