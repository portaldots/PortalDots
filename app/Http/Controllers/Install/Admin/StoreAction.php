<?php

namespace App\Http\Controllers\Install\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Install\RunInstallService;

class StoreAction extends Controller
{
    /**
     * @var RunInstallService
     */
    private $runInstallService;

    public function __construct(RunInstallService $runInstallService)
    {
        $this->runInstallService = $runInstallService;
    }

    public function __invoke()
    {
        $this->runInstallService->run();
    }
}
