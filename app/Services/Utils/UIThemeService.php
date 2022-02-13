<?php

declare(strict_types=1);

namespace App\Services\Utils;

use Illuminate\Support\Facades\Cookie;

class UIThemeService
{
    private const COOKIE_KEY = 'ui_theme';

    public const AVAILABLE_THEMES = [
        'system',
        'light',
        'dark',
    ];

    public static function getCurrentTheme(): string
    {
        $theme = Cookie::get(self::COOKIE_KEY, 'system');

        if (in_array($theme, UIThemeService::AVAILABLE_THEMES, true)) {
            return $theme;
        }

        return 'system';
    }

    public function setCurrentTheme(string $theme)
    {
        if (in_array($theme, UIThemeService::AVAILABLE_THEMES, true)) {
            Cookie::queue(self::COOKIE_KEY, $theme, 60 * 60 * 24 * 365);
        }
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

    public static function getCssPrimaryColor(float $alpha = 1, bool $isDark = false): string
    {
        $hsl = config('portal.primary_color_hsl');

        if (!isset($hsl[0]) && !isset($hsl[1]) && !isset($hsl[2])) {
            $hsl = [214, 91, 53];
        }

        if ($isDark) {
            $s = max($hsl[1] - 10, 0);
            $l = min($hsl[2] + 20, 100);
            return "hsla({$hsl[0]}, {$s}%, {$l}%, {$alpha})";
        } else {
            return "hsla({$hsl[0]}, {$hsl[1]}%, {$hsl[2]}%, {$alpha})";
        }
    }
}
