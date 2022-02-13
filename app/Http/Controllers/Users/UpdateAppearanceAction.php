<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Services\Utils\UIThemeService;
use Illuminate\Http\Request;

class UpdateAppearanceAction extends Controller
{
    /**
     * @var UIThemeService
     */
    public $uiThemeService;

    public function __construct(UIThemeService $uiThemeService)
    {
        $this->uiThemeService = $uiThemeService;
    }

    public function __invoke(Request $request)
    {
        $newTheme = $request->theme;
        if (in_array($newTheme, UIThemeService::AVAILABLE_THEMES, true)) {
            $this->uiThemeService->setCurrentTheme($newTheme);
        }
        return redirect()
            ->route('user.appearance')
            ->with('topAlert.title', '変更を保存しました');
    }
}
