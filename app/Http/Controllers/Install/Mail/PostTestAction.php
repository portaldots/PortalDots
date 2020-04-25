<?php

namespace App\Http\Controllers\Install\Mail;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Install\RunInstallService;

class PostTestAction extends Controller
{
    /**
     * @var RunInstallService
     */
    private $editor;

    public function __construct(RunInstallService $runInstallService)
    {
        $this->runInstallService = $runInstallService;
    }

    public function __invoke(Request $request)
    {
        if (empty(session('install_password'))) {
            return redirect()
                ->route('install.mail.edit');
        }

        if (session('install_password') !== $request->install_password) {
            return redirect()
                ->route('install.mail.test')
                ->with('topAlert.type', 'danger')
                ->with('topAlert.title', 'パスワードが違います');
        }

        $this->runInstallService->run();
    }
}
