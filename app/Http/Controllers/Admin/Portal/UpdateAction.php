<?php

namespace App\Http\Controllers\Admin\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\Install\PortalRequest;
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

    public function __invoke(PortalRequest $request)
    {
        $info = $request->all();
        $info['APP_FORCE_HTTPS'] = isset($info['APP_FORCE_HTTPS']) && $info['APP_FORCE_HTTPS'] === '1'
            ? 'true' : 'false';
        $this->portalService->updateInfo($info);
        return redirect()
            ->back()
            ->with('topAlert.title', '変更を保存しました');
    }
}
