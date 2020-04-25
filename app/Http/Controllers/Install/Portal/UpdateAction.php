<?php

namespace App\Http\Controllers\Install\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Install\PortalService;

class UpdateAction extends Controller
{
    /**
     * @var PortalService
     */
    private $editor;

    public function __construct(PortalService $portalService)
    {
        $this->portalService = $portalService;
    }

    public function __invoke(Request $request)
    {
        $this->portalService->updateInfo($request->all());
        return redirect()
            ->route('install.database.edit');
    }
}
