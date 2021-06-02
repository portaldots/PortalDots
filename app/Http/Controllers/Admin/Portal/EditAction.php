<?php

namespace App\Http\Controllers\Admin\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Install\PortalService;

class EditAction extends Controller
{
    /**
     * @var PortalService
     */
    private $portalService;

    public function __construct(PortalService $portalService)
    {
        $this->portalService = $portalService;
    }

    public function __invoke()
    {
        return view('admin.portal.form')
            ->with('portal', $this->portalService->getInfo());
    }
}
