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

    public static function getCssColorScheme(): string
    {
        if (static::getCurrentTheme() === 'light') {
            return 'light';
        } elseif (static::getCurrentTheme() === 'dark') {
            return 'dark';
        } else {
            return 'light dark';
        }
    }
}
