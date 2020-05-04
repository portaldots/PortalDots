<?php

namespace App\Http\Controllers\Install\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Install\PortalService;

class EditAction extends Controller
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
        $url = url('/');

        if (strpos($url, 'http:') !== 0 && strpos($url, 'https:') !== 0) {
            $url = ($request->isSecure() ? 'https:' : 'http:') . $url;
        }

        return view('v2.install.portal.form')
            ->with('portal', $this->portalService->getInfo())
            ->with('suggested_app_url', $url);
    }
}
