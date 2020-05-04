<?php

namespace App\Http\Controllers\Install\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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

    public function __invoke(Request $request)
    {
        $this->runInstallService->run();

        $user = $this->registerService->register(
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
    }
}
