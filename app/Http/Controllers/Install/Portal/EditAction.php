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
        // 開発環境で http: が表示されないことがあるが、開発環境以外では
        // 正常に表示されるので問題がない
        $url = url('/');

        return view('v2.install.portal.form')
            ->with('portal', $this->portalService->getInfo())
            ->with('suggested_app_url', $url);
    }
}
