<?php

declare(strict_types=1);

namespace App\Services\Utils;

class UIThemeService
{
    public static function getCurrentTheme(): string
    {
        // light, dark, system のどれか
        return 'light';
    }
}
