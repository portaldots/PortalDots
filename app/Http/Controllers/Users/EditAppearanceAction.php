<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Services\Utils\UIThemeService;
use Illuminate\Http\Request;

class EditAppearanceAction extends Controller
{
    /**
     * @var UIThemeService
     */
    public $uiThemeService;

    public function __construct(UIThemeService $uiThemeService)
    {
        $this->uiThemeService = $uiThemeService;
    }

    public function __invoke()
    {
        return view('users.appearance')
            ->with('theme', $this->uiThemeService->getCurrentTheme());
    }
}
